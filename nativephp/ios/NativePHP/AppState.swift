import SwiftUI

/// Manages the app initialization state.
/// Uses two phases:
/// 1. isReadyToLoad - deferred init complete, safe to render ContentView/WebView
/// 2. isInitialized - WebView has loaded, safe to dismiss splash
@MainActor
class AppState: ObservableObject {
    static let shared = AppState()

    /// Phase 1: Deferred initialization complete, WebView can be rendered and start loading
    @Published var isReadyToLoad = false

    /// Phase 2: WebView has finished loading, splash can be dismissed
    @Published var isInitialized = false

    private init() {}

    /// Mark that deferred initialization is complete and WebView can start loading
    func markReadyToLoad() {
        DebugLogger.shared.log("📱 AppState: ready to load WebView")
        isReadyToLoad = true
    }

    /// Mark that WebView has loaded and splash can be dismissed
    func markInitialized() {
        DebugLogger.shared.log("📱 AppState: marking as initialized")
        withAnimation(.easeInOut(duration: 0.3)) {
            isInitialized = true
        }
    }
}
