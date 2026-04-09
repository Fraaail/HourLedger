import SwiftUI
import AVFoundation
import UserNotifications

// MARK: - App Lifecycle Notification Names
// Plugins can subscribe to these notifications to receive iOS lifecycle events

extension Notification.Name {
    /// Posted when app receives APNS device token
    /// userInfo: ["deviceToken": Data]
    static let didRegisterForRemoteNotifications = Notification.Name("NativePHP.didRegisterForRemoteNotifications")

    /// Posted when app fails to register for remote notifications
    /// userInfo: ["error": Error]
    static let didFailToRegisterForRemoteNotifications = Notification.Name("NativePHP.didFailToRegisterForRemoteNotifications")

    /// Posted when app receives a remote notification
    /// userInfo: ["payload": [AnyHashable: Any]]
    static let didReceiveRemoteNotification = Notification.Name("NativePHP.didReceiveRemoteNotification")

    /// Posted when app finishes launching
    /// userInfo: ["launchOptions": [UIApplication.LaunchOptionsKey: Any]?]
    static let didFinishLaunching = Notification.Name("NativePHP.didFinishLaunching")

    /// Posted when app becomes active
    static let didBecomeActive = Notification.Name("NativePHP.didBecomeActive")

    /// Posted when app enters background
    static let didEnterBackground = Notification.Name("NativePHP.didEnterBackground")
}

class AppDelegate: NSObject, UIApplicationDelegate {
    static let shared = AppDelegate()

    // Called when the app is launched
    func application(
        _ application: UIApplication,
        didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]? = nil
    ) -> Bool {
        // Check if the app was launched from a URL (custom scheme)
        if let url = launchOptions?[UIApplication.LaunchOptionsKey.url] as? URL {
            DebugLogger.shared.log("📱 AppDelegate: Cold start with custom scheme URL: \(url)")
            // Pass the URL to the DeepLinkRouter
            DeepLinkRouter.shared.handle(url: url)
        }

        // Check if the app was launched from a Universal Link
        if let userActivityDictionary = launchOptions?[UIApplication.LaunchOptionsKey.userActivityDictionary] as? [String: Any],
           let userActivity = userActivityDictionary["UIApplicationLaunchOptionsUserActivityKey"] as? NSUserActivity,
           userActivity.activityType == NSUserActivityTypeBrowsingWeb,
           let url = userActivity.webpageURL {
            DebugLogger.shared.log("📱 AppDelegate: Cold start with Universal Link: \(url)")
            // Pass the URL to the DeepLinkRouter
            DeepLinkRouter.shared.handle(url: url)
        }

        // Check if the app was launched from a Home Screen Quick Action
        if let shortcutItem = launchOptions?[UIApplication.LaunchOptionsKey.shortcutItem] as? UIApplicationShortcutItem {
            DebugLogger.shared.log("📱 AppDelegate: Cold start with Quick Action: \(shortcutItem.type)")
            _ = handleQuickAction(shortcutItem)
        }

        return true
    }

    // Called for Universal Links
    func application(
        _ application: UIApplication,
        continue userActivity: NSUserActivity,
        restorationHandler: @escaping ([UIUserActivityRestoring]?) -> Void
    ) -> Bool {
        // Check if this is a Universal Link
        if userActivity.activityType == NSUserActivityTypeBrowsingWeb,
           let url = userActivity.webpageURL {
            // Pass the URL to the DeepLinkRouter
            DeepLinkRouter.shared.handle(url: url)
            return true
        }

        return false
    }

    // MARK: - Push Notification Token Handling (forwards to plugins via NotificationCenter)

    func application(
        _ application: UIApplication,
        didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data
    ) {
        NotificationCenter.default.post(
            name: .didRegisterForRemoteNotifications,
            object: nil,
            userInfo: ["deviceToken": deviceToken]
        )
    }

    func application(
        _ application: UIApplication,
        didFailToRegisterForRemoteNotificationsWithError error: Error
    ) {
        NotificationCenter.default.post(
            name: .didFailToRegisterForRemoteNotifications,
            object: nil,
            userInfo: ["error": error]
        )
    }

    // Handle deeplinks
    func application(
        _ app: UIApplication,
        open url: URL,
        options: [UIApplication.OpenURLOptionsKey: Any] = [:]
    ) -> Bool {
        // Pass the URL to the DeepLinkRouter
        DeepLinkRouter.shared.handle(url: url)
        return true
    }

    func application(
        _ application: UIApplication,
        performActionFor shortcutItem: UIApplicationShortcutItem,
        completionHandler: @escaping (Bool) -> Void
    ) {
        completionHandler(handleQuickAction(shortcutItem))
    }

    private func handleQuickAction(_ shortcutItem: UIApplicationShortcutItem) -> Bool {
        guard let route = shortcutRoute(for: shortcutItem) else {
            return false
        }

        let normalizedRoute = route.hasPrefix("/") ? String(route.dropFirst()) : route

        guard !normalizedRoute.isEmpty,
              let url = URL(string: "nativephp://\(normalizedRoute)") else {
            return false
        }

        DeepLinkRouter.shared.handle(url: url)

        return true
    }

    private func shortcutRoute(for shortcutItem: UIApplicationShortcutItem) -> String? {
        if let route = shortcutItem.userInfo?["route"] as? String, !route.isEmpty {
            return route
        }

        switch shortcutItem.type {
        case "com.nativephp.app.clock-in":
            return "/shortcut/clock-in"
        case "com.nativephp.app.clock-out":
            return "/shortcut/clock-out"
        default:
            return nil
        }
    }
}

final class CriticalUnderHoursAlertScheduler {
    static let shared = CriticalUnderHoursAlertScheduler()

    private let notificationCenter = UNUserNotificationCenter.current()
    private let notificationId = "hourledger_under_hours_critical_alert"

    private init() {}

    func sync(fromPayloadJson payloadJson: String?) {
        guard let payloadJson = payloadJson, !payloadJson.isEmpty else {
            cancel()
            return
        }

        guard
            let payloadData = payloadJson.data(using: .utf8),
            let payload = try? JSONSerialization.jsonObject(with: payloadData) as? [String: Any]
        else {
            cancel()
            return
        }

        sync(from: payload)
    }

    func sync(from payload: [String: Any]) {
        let enabled = boolValue(payload["enabled"], defaultValue: false)

        if !enabled {
            cancel()
            return
        }

        let profileName = stringValue(payload["profile_name"], defaultValue: "HourLedger")
        let timezoneId = stringValue(payload["timezone"], defaultValue: TimeZone.current.identifier)
        let timezone = TimeZone(identifier: timezoneId) ?? .current
        let requiredMinutes = intValue(payload["required_minutes"], defaultValue: 480).clamped(to: 60...960)
        let todayTotalMinutes = max(0, intValue(payload["today_total_minutes"], defaultValue: 0))
        let hour = intValue(payload["hour"], defaultValue: 18).clamped(to: 0...23)
        let minute = intValue(payload["minute"], defaultValue: 0).clamped(to: 0...59)
        let underHours = boolValue(payload["under_hours"], defaultValue: todayTotalMinutes < requiredMinutes)

        if !underHours {
            cancel()
            return
        }

        requestAuthorizationAndSchedule(
            profileName: profileName,
            timezone: timezone,
            requiredMinutes: requiredMinutes,
            todayTotalMinutes: todayTotalMinutes,
            hour: hour,
            minute: minute
        )
    }

    func cancel() {
        notificationCenter.removePendingNotificationRequests(withIdentifiers: [notificationId])
        notificationCenter.removeDeliveredNotifications(withIdentifiers: [notificationId])
    }

    private func requestAuthorizationAndSchedule(
        profileName: String,
        timezone: TimeZone,
        requiredMinutes: Int,
        todayTotalMinutes: Int,
        hour: Int,
        minute: Int
    ) {
        let options: UNAuthorizationOptions

        if #available(iOS 12.0, *) {
            options = [.alert, .badge, .sound, .criticalAlert]
        } else {
            options = [.alert, .badge, .sound]
        }

        notificationCenter.requestAuthorization(options: options) { [weak self] granted, _ in
            guard granted else {
                return
            }

            self?.scheduleLocalNotification(
                profileName: profileName,
                timezone: timezone,
                requiredMinutes: requiredMinutes,
                todayTotalMinutes: todayTotalMinutes,
                hour: hour,
                minute: minute
            )
        }
    }

    private func scheduleLocalNotification(
        profileName: String,
        timezone: TimeZone,
        requiredMinutes: Int,
        todayTotalMinutes: Int,
        hour: Int,
        minute: Int
    ) {
        var calendar = Calendar(identifier: .gregorian)
        calendar.timeZone = timezone

        let now = Date()
        var baseComponents = calendar.dateComponents([.year, .month, .day], from: now)
        baseComponents.hour = hour
        baseComponents.minute = minute
        baseComponents.second = 0

        var targetDate = calendar.date(from: baseComponents) ?? now

        if targetDate <= now {
            targetDate = calendar.date(byAdding: .day, value: 1, to: targetDate) ?? targetDate
        }

        while calendar.isDateInWeekend(targetDate) {
            targetDate = calendar.date(byAdding: .day, value: 1, to: targetDate) ?? targetDate
        }

        var triggerComponents = calendar.dateComponents([.year, .month, .day, .hour, .minute, .second], from: targetDate)
        triggerComponents.timeZone = timezone

        let renderedHours = String(format: "%.1f", Double(todayTotalMinutes) / 60.0)
        let requiredHours = String(format: "%.1f", Double(requiredMinutes) / 60.0)

        let content = UNMutableNotificationContent()
        content.title = "Under Target Hours"
        content.body = "\(profileName) is below target hours today (\(renderedHours)h of \(requiredHours)h). Open HourLedger to review your entries."

        if #available(iOS 12.0, *) {
            content.sound = .defaultCriticalSound(withAudioVolume: 1.0)
        } else {
            content.sound = .default
        }

        let trigger = UNCalendarNotificationTrigger(dateMatching: triggerComponents, repeats: false)
        let request = UNNotificationRequest(identifier: notificationId, content: content, trigger: trigger)

        notificationCenter.removePendingNotificationRequests(withIdentifiers: [notificationId])
        notificationCenter.add(request, withCompletionHandler: nil)
    }

    private func boolValue(_ value: Any?, defaultValue: Bool) -> Bool {
        switch value {
        case let bool as Bool:
            return bool
        case let number as NSNumber:
            return number.boolValue
        case let string as String:
            return ["1", "true", "yes", "on"].contains(string.lowercased())
        default:
            return defaultValue
        }
    }

    private func intValue(_ value: Any?, defaultValue: Int) -> Int {
        switch value {
        case let int as Int:
            return int
        case let number as NSNumber:
            return number.intValue
        case let string as String:
            return Int(string) ?? defaultValue
        default:
            return defaultValue
        }
    }

    private func stringValue(_ value: Any?, defaultValue: String) -> String {
        if let string = value as? String, !string.isEmpty {
            return string
        }

        return defaultValue
    }
}

private extension Int {
    func clamped(to range: ClosedRange<Int>) -> Int {
        return min(range.upperBound, max(range.lowerBound, self))
    }
}
