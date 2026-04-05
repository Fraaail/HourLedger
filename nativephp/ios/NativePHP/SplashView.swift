import SwiftUI

/// A view that replicates the launch screen appearance.
/// This allows the app to appear launched while heavy initialization continues in the background.
struct SplashView: View {
    var body: some View {
        GeometryReader { geometry in
            ZStack {
                // Background color matching launch screen (white)
                Color.white
                    .ignoresSafeArea()

                // LaunchImage scaled to fill (matching LaunchScreen.storyboard scaleAspectFill)
                Image("LaunchImage")
                    .resizable()
                    .aspectRatio(contentMode: .fill)
                    .frame(width: geometry.size.width, height: geometry.size.height)
                    .clipped()
                    .ignoresSafeArea()
            }
        }
        .ignoresSafeArea()
    }
}

#Preview {
    SplashView()
}
