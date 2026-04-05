import Foundation
import Network

class HotReloadServer {
    private var listener: NWListener?
    private let port: NWEndpoint.Port = 9999
    private let queue = DispatchQueue(label: "HotReloadServer")

    static let shared = HotReloadServer()

    private init() {}

    func start() {
        guard listener == nil else { return }

        do {
            listener = try NWListener(using: .tcp, on: port)
            listener?.newConnectionHandler = { [weak self] connection in
                self?.handleConnection(connection)
            }

            listener?.start(queue: queue)
            print("🔥 Hot reload server started on port \(port)")
        } catch {
            print("❌ Failed to start hot reload server: \(error)")
        }
    }

    func stop() {
        listener?.cancel()
        listener = nil
        print("🔥 Hot reload server stopped")
    }

    private func handleConnection(_ connection: NWConnection) {
        connection.start(queue: queue)

        // Any connection triggers a reload
        DispatchQueue.main.async {
            NotificationCenter.default.post(name: .reloadWebViewNotification, object: nil)
        }

        // Immediately close the connection
        connection.cancel()
        print("🔄 Hot reload triggered")
    }
}
