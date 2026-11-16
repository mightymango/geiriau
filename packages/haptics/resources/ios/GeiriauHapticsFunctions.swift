import Foundation
import UIKit

/// Namespace: "GeiriauHaptics.*"
///
/// Feedback generators must run on the main thread; bridge calls may not
/// arrive there, so each one hops to main and returns straight away.
enum GeiriauHapticsFunctions {

    /// Parameters: style — "light" (default), "medium" or "heavy"
    class Impact: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let style: UIImpactFeedbackGenerator.FeedbackStyle
            switch parameters["style"] as? String ?? "light" {
            case "medium": style = .medium
            case "heavy": style = .heavy
            default: style = .light
            }

            DispatchQueue.main.async {
                UIImpactFeedbackGenerator(style: style).impactOccurred()
            }

            return BridgeResponse.success(data: ["success": true])
        }
    }

    /// Parameters: type — "success" (default), "warning" or "error"
    class Notify: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let type: UINotificationFeedbackGenerator.FeedbackType
            switch parameters["type"] as? String ?? "success" {
            case "warning": type = .warning
            case "error": type = .error
            default: type = .success
            }

            DispatchQueue.main.async {
                UINotificationFeedbackGenerator().notificationOccurred(type)
            }

            return BridgeResponse.success(data: ["success": true])
        }
    }

    class Selection: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            DispatchQueue.main.async {
                UISelectionFeedbackGenerator().selectionChanged()
            }

            return BridgeResponse.success(data: ["success": true])
        }
    }
}
