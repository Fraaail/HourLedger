import WidgetKit
import SwiftUI

private let homeWidgetAppGroup = "group.com.nativephp.hourledger"
private let homeWidgetSnapshotStorageKey = "hourledger.home_widget.snapshot"

struct HourLedgerWidgetSnapshot: Codable {
    let profileName: String
    let statusLabel: String
    let totalHours: String
    let totalDays: String
    let clockedInAt: String
    let updatedAt: String

    static var empty: HourLedgerWidgetSnapshot {
        HourLedgerWidgetSnapshot(
            profileName: "Default",
            statusLabel: "Clocked Out",
            totalHours: "0.0",
            totalDays: "0",
            clockedInAt: "--",
            updatedAt: ""
        )
    }
}

struct HourLedgerWidgetEntry: TimelineEntry {
    let date: Date
    let snapshot: HourLedgerWidgetSnapshot
}

struct HourLedgerWidgetProvider: TimelineProvider {
    func placeholder(in context: Context) -> HourLedgerWidgetEntry {
        HourLedgerWidgetEntry(date: Date(), snapshot: .empty)
    }

    func getSnapshot(in context: Context, completion: @escaping (HourLedgerWidgetEntry) -> Void) {
        completion(HourLedgerWidgetEntry(date: Date(), snapshot: loadSnapshot()))
    }

    func getTimeline(in context: Context, completion: @escaping (Timeline<HourLedgerWidgetEntry>) -> Void) {
        let entry = HourLedgerWidgetEntry(date: Date(), snapshot: loadSnapshot())
        let refreshDate = Calendar.current.date(byAdding: .minute, value: 20, to: Date()) ?? Date().addingTimeInterval(1200)
        completion(Timeline(entries: [entry], policy: .after(refreshDate)))
    }

    private func loadSnapshot() -> HourLedgerWidgetSnapshot {
        guard
            let defaults = UserDefaults(suiteName: homeWidgetAppGroup),
            let data = defaults.data(forKey: homeWidgetSnapshotStorageKey),
            let snapshot = try? JSONDecoder().decode(HourLedgerWidgetSnapshot.self, from: data)
        else {
            return .empty
        }

        return snapshot
    }
}

struct HourLedgerHomeWidget: Widget {
    let kind: String = "HourLedgerHomeWidget"

    var body: some WidgetConfiguration {
        StaticConfiguration(kind: kind, provider: HourLedgerWidgetProvider()) { entry in
            HourLedgerHomeWidgetView(entry: entry)
        }
        .configurationDisplayName("HourLedger")
        .description("See total rendered hours and current status at a glance.")
        .supportedFamilies([.systemSmall, .systemMedium])
    }
}

struct HourLedgerHomeWidgetView: View {
    var entry: HourLedgerWidgetProvider.Entry

    var body: some View {
        ZStack {
            LinearGradient(
                colors: [Color(red: 0.05, green: 0.07, blue: 0.10), Color(red: 0.10, green: 0.13, blue: 0.18)],
                startPoint: .topLeading,
                endPoint: .bottomTrailing
            )

            VStack(alignment: .leading, spacing: 6) {
                Text(entry.snapshot.profileName)
                    .font(.system(size: 13, weight: .semibold, design: .rounded))
                    .foregroundColor(.white)
                    .lineLimit(1)

                Text(entry.snapshot.statusLabel)
                    .font(.system(size: 11, weight: .medium, design: .rounded))
                    .foregroundColor(.white.opacity(0.75))
                    .lineLimit(1)

                Spacer(minLength: 4)

                Text("Hours: \(entry.snapshot.totalHours)")
                    .font(.system(size: 20, weight: .bold, design: .rounded))
                    .foregroundColor(.white)

                Text("Days: \(entry.snapshot.totalDays)")
                    .font(.system(size: 12, weight: .medium, design: .rounded))
                    .foregroundColor(.white.opacity(0.8))

                Text("In at: \(entry.snapshot.clockedInAt)")
                    .font(.system(size: 11, weight: .regular, design: .rounded))
                    .foregroundColor(.white.opacity(0.7))
                    .lineLimit(1)
            }
            .padding(12)
        }
        .containerBackground(.clear, for: .widget)
    }
}
