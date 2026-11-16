package com.geiriau.plugins.haptics

import android.content.Context
import android.os.Build
import android.os.VibrationEffect
import android.os.Vibrator
import android.os.VibratorManager
import com.nativephp.mobile.bridge.BridgeFunction

private fun vibrator(context: Context): Vibrator? =
    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
        (context.getSystemService(Context.VIBRATOR_MANAGER_SERVICE) as? VibratorManager)?.defaultVibrator
    } else {
        @Suppress("DEPRECATION")
        context.getSystemService(Context.VIBRATOR_SERVICE) as? Vibrator
    }

private fun play(context: Context, predefined: Int, fallbackMs: Long) {
    val vibrator = vibrator(context) ?: return

    if (!vibrator.hasVibrator()) {
        return
    }

    val effect = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.Q) {
        VibrationEffect.createPredefined(predefined)
    } else {
        VibrationEffect.createOneShot(fallbackMs, VibrationEffect.DEFAULT_AMPLITUDE)
    }

    vibrator.vibrate(effect)
}

private fun playPattern(context: Context, timings: LongArray) {
    val vibrator = vibrator(context) ?: return

    if (!vibrator.hasVibrator()) {
        return
    }

    vibrator.vibrate(VibrationEffect.createWaveform(timings, -1))
}

/** Namespace: "GeiriauHaptics.*" */
object GeiriauHapticsFunctions {

    class Impact(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            when (parameters["style"] as? String ?: "light") {
                "heavy" -> play(context, VibrationEffect.EFFECT_HEAVY_CLICK, 40)
                "medium" -> play(context, VibrationEffect.EFFECT_CLICK, 25)
                else -> play(context, VibrationEffect.EFFECT_TICK, 10)
            }

            return mapOf("success" to true)
        }
    }

    class Notify(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            when (parameters["type"] as? String ?: "success") {
                "error" -> playPattern(context, longArrayOf(0, 30, 60, 30, 60, 30))
                "warning" -> playPattern(context, longArrayOf(0, 30, 80, 30))
                else -> play(context, VibrationEffect.EFFECT_DOUBLE_CLICK, 30)
            }

            return mapOf("success" to true)
        }
    }

    class Selection(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            play(context, VibrationEffect.EFFECT_TICK, 10)

            return mapOf("success" to true)
        }
    }
}
