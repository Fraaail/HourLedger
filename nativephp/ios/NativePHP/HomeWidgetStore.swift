import Foundation

#if canImport(WidgetKit)
import WidgetKit
#endif

struct HomeWidgetSnapshot: Codable {
    let profileName: String
    let statusLabel: String
    let totalHours: String
    let totalDays: String
    let clockedInAt: String
    let updatedAt: String
}

final class HomeWidgetStore {
    static let shared = HomeWidgetStore()

    private let appGroupSuiteName = "group.com.nativephp.hourledger"
    private let snapshotStorageKey = "hourledger.home_widget.snapshot"

    private init() {}

    func sync(fromPayloadJson payloadJson: String?) {
        guard let payloadJson = payloadJson, !payloadJson.isEmpty else {
            clear()
            return
        }

        guard
            let payloadData = payloadJson.data(using: .utf8),
            let payload = try? JSONSerialization.jsonObject(with: payloadData) as? [String: Any]
        else {
            return
        }

        let snapshot = HomeWidgetSnapshot(
            profileName: stringValue(payload["profile_name"], defaultValue: "Default"),
            statusLabel: stringValue(payload["status_label"], defaultValue: "Clocked Out"),
            totalHours: formatHours(payload["total_hours"]),
            totalDays: stringValue(payload["total_days"], defaultValue: "0"),
            clockedInAt: stringValue(payload["clocked_in_at"], defaultValue: "--"),
            updatedAt: stringValue(payload["updated_at"], defaultValue: "")
        )

        save(snapshot)
    }

    func clear() {
        let defaults = userDefaults()
        defaults.removeObject(forKey: snapshotStorageKey)
        refreshWidgetTimelines()
    }

    private func save(_ snapshot: HomeWidgetSnapshot) {
        let defaults = userDefaults()

        guard let encodedSnapshot = try? JSONEncoder().encode(snapshot) else {
            return
        }

        defaults.set(encodedSnapshot, forKey: snapshotStorageKey)
        defaults.synchronize()

        refreshWidgetTimelines()
    }

    private func userDefaults() -> UserDefaults {
        return UserDefaults(suiteName: appGroupSuiteName) ?? .standard
    }

    private func formatHours(_ value: Any?) -> String {
        if let doubleValue = value as? Double {
            return String(format: "%.1f", doubleValue)
        }

        if let numberValue = value as? NSNumber {
            return String(format: "%.1f", numberValue.doubleValue)
        }

        if let stringValue = value as? String, let parsedDouble = Double(stringValue) {
            return String(format: "%.1f", parsedDouble)
        }

        return "0.0"
    }

    private func stringValue(_ value: Any?, defaultValue: String) -> String {
        if let stringValue = value as? String, !stringValue.isEmpty {
            return stringValue
        }

        if let numberValue = value as? NSNumber {
            return numberValue.stringValue
        }

        return defaultValue
    }

    private func refreshWidgetTimelines() {
        #if canImport(WidgetKit)
        if #available(iOS 14.0, *) {
            WidgetCenter.shared.reloadAllTimelines()
        }
        #endif
    }
}
