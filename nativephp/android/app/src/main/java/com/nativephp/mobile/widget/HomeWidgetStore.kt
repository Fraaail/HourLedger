package com.nativephp.mobile.widget

import android.content.Context
import org.json.JSONObject

data class WidgetSnapshot(
    val profileName: String,
    val statusLabel: String,
    val totalHours: String,
    val totalDays: String,
    val clockedInAt: String,
    val updatedAt: String
)

object HomeWidgetStore {
    private const val PREFS_NAME = "hourledger_widget"
    private const val KEY_PROFILE_NAME = "profile_name"
    private const val KEY_STATUS_LABEL = "status_label"
    private const val KEY_TOTAL_HOURS = "total_hours"
    private const val KEY_TOTAL_DAYS = "total_days"
    private const val KEY_CLOCKED_IN_AT = "clocked_in_at"
    private const val KEY_UPDATED_AT = "updated_at"

    fun saveFromPayload(context: Context, payloadJson: String?) {
        if (payloadJson.isNullOrBlank()) return

        try {
            val json = JSONObject(payloadJson)
            val hoursValue = if (json.has("total_hours")) {
                val raw = json.optDouble("total_hours", 0.0)
                String.format("%.1f", raw)
            } else {
                "0.0"
            }

            val prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            prefs.edit()
                .putString(KEY_PROFILE_NAME, json.optString("profile_name", "Default"))
                .putString(KEY_STATUS_LABEL, json.optString("status_label", "Clocked Out"))
                .putString(KEY_TOTAL_HOURS, hoursValue)
                .putString(KEY_TOTAL_DAYS, json.optString("total_days", "0"))
                .putString(KEY_CLOCKED_IN_AT, json.optString("clocked_in_at", "--"))
                .putString(KEY_UPDATED_AT, json.optString("updated_at", ""))
                .apply()
        } catch (_: Exception) {
            // Keep previous widget state if payload is malformed.
        }
    }

    fun snapshot(context: Context): WidgetSnapshot {
        val prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)

        return WidgetSnapshot(
            profileName = prefs.getString(KEY_PROFILE_NAME, "Default") ?: "Default",
            statusLabel = prefs.getString(KEY_STATUS_LABEL, "Clocked Out") ?: "Clocked Out",
            totalHours = prefs.getString(KEY_TOTAL_HOURS, "0.0") ?: "0.0",
            totalDays = prefs.getString(KEY_TOTAL_DAYS, "0") ?: "0",
            clockedInAt = prefs.getString(KEY_CLOCKED_IN_AT, "--") ?: "--",
            updatedAt = prefs.getString(KEY_UPDATED_AT, "") ?: ""
        )
    }
}
