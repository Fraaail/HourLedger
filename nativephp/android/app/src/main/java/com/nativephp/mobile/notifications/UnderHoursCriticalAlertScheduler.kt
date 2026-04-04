package com.nativephp.mobile.notifications

import android.Manifest
import android.app.AlarmManager
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import androidx.core.content.ContextCompat
import java.time.ZoneId
import java.time.ZonedDateTime

object UnderHoursCriticalAlertScheduler {
    private const val ALERT_REQUEST_CODE = 7501
    private const val PREFS_NAME = "hourledger_under_hours_alert_refresh"
    private const val PREF_TIMEZONE = "timezone"
    private const val PREF_PROFILE_NAME = "profile_name"
    private const val PREF_REQUIRED_MINUTES = "required_minutes"
    private const val PREF_TODAY_TOTAL_MINUTES = "today_total_minutes"
    private const val PREF_HOUR = "hour"
    private const val PREF_MINUTE = "minute"
    private const val PREF_SNAPSHOT_DATE = "snapshot_date"
    const val CHANNEL_ID = "hourledger_under_hours_alerts"
    const val ACTION_ALERT = "com.nativephp.mobile.action.UNDER_HOURS_CRITICAL_ALERT"
    const val EXTRA_PROFILE_NAME = "profile_name"
    const val EXTRA_TIMEZONE = "timezone"
    const val EXTRA_REQUIRED_MINUTES = "required_minutes"
    const val EXTRA_TODAY_TOTAL_MINUTES = "today_total_minutes"
    const val EXTRA_HOUR = "hour"
    const val EXTRA_MINUTE = "minute"

    fun sync(
        context: Context,
        enabled: Boolean,
        timezone: String,
        profileName: String,
        requiredMinutes: Int,
        todayTotalMinutes: Int,
        hour: Int,
        minute: Int
    ) {
        if (!enabled) {
            cancel(context)
            return
        }

        val safeRequiredMinutes = requiredMinutes.coerceIn(60, 960)
        val safeTodayTotalMinutes = todayTotalMinutes.coerceAtLeast(0)
        val safeHour = hour.coerceIn(0, 23)
        val safeMinute = minute.coerceIn(0, 59)
        val zoneId = resolveZoneId(timezone)
        val now = ZonedDateTime.now(zoneId)
        val todayDate = now.toLocalDate().toString()

        persistConfiguration(
            context = context,
            timezone = timezone,
            profileName = profileName,
            requiredMinutes = safeRequiredMinutes,
            todayTotalMinutes = safeTodayTotalMinutes,
            hour = safeHour,
            minute = safeMinute,
            snapshotDate = todayDate
        )

        createNotificationChannel(context)

        var target = now
            .withHour(safeHour)
            .withMinute(safeMinute)
            .withSecond(0)
            .withNano(0)

        val shouldScheduleToday = safeTodayTotalMinutes < safeRequiredMinutes

        if (!shouldScheduleToday || !target.isAfter(now)) {
            target = target.plusDays(1)
        }

        while (target.dayOfWeek.value >= 6) {
            target = target.plusDays(1)
        }

        val triggerAtMillis = target.toInstant().toEpochMilli()

        val alarmManager = context.getSystemService(Context.ALARM_SERVICE) as AlarmManager
        val pendingIntent = buildPendingIntent(
            context,
            profileName,
            timezone,
            safeRequiredMinutes,
            safeTodayTotalMinutes,
            safeHour,
            safeMinute
        )

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S && !alarmManager.canScheduleExactAlarms()) {
            alarmManager.setAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerAtMillis, pendingIntent)
            return
        }

        alarmManager.setExactAndAllowWhileIdle(AlarmManager.RTC_WAKEUP, triggerAtMillis, pendingIntent)
    }

    fun cancel(context: Context) {
        clearConfiguration(context)

        val alarmManager = context.getSystemService(Context.ALARM_SERVICE) as AlarmManager
        val pendingIntent = buildPendingIntent(
            context,
            "HourLedger",
            "UTC",
            480,
            0,
            18,
            0
        )

        alarmManager.cancel(pendingIntent)

        val notificationManager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        notificationManager.cancel(UnderHoursCriticalAlertReceiver.NOTIFICATION_ID)
    }

    fun refreshFromStorage(context: Context): Boolean {
        val prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)

        if (!prefs.contains(PREF_TIMEZONE) || !prefs.contains(PREF_PROFILE_NAME) || !prefs.contains(PREF_REQUIRED_MINUTES)) {
            return false
        }

        val timezone = prefs.getString(PREF_TIMEZONE, "UTC") ?: "UTC"
        val profileName = prefs.getString(PREF_PROFILE_NAME, "HourLedger") ?: "HourLedger"
        val requiredMinutes = prefs.getInt(PREF_REQUIRED_MINUTES, 480)
        var todayTotalMinutes = prefs.getInt(PREF_TODAY_TOTAL_MINUTES, 0)
        val hour = prefs.getInt(PREF_HOUR, 18)
        val minute = prefs.getInt(PREF_MINUTE, 0)
        val snapshotDate = prefs.getString(PREF_SNAPSHOT_DATE, null)

        val todayDate = ZonedDateTime.now(resolveZoneId(timezone)).toLocalDate().toString()

        if (snapshotDate != todayDate) {
            todayTotalMinutes = 0
        }

        sync(
            context = context,
            enabled = true,
            timezone = timezone,
            profileName = profileName,
            requiredMinutes = requiredMinutes,
            todayTotalMinutes = todayTotalMinutes,
            hour = hour,
            minute = minute
        )

        return true
    }

    fun canPostNotifications(context: Context): Boolean {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU) {
            return true
        }

        return ContextCompat.checkSelfPermission(
            context,
            Manifest.permission.POST_NOTIFICATIONS
        ) == PackageManager.PERMISSION_GRANTED
    }

    private fun buildPendingIntent(
        context: Context,
        profileName: String,
        timezone: String,
        requiredMinutes: Int,
        todayTotalMinutes: Int,
        hour: Int,
        minute: Int
    ): PendingIntent {
        val intent = Intent(context, UnderHoursCriticalAlertReceiver::class.java).apply {
            action = ACTION_ALERT
            putExtra(EXTRA_PROFILE_NAME, profileName)
            putExtra(EXTRA_TIMEZONE, timezone)
            putExtra(EXTRA_REQUIRED_MINUTES, requiredMinutes)
            putExtra(EXTRA_TODAY_TOTAL_MINUTES, todayTotalMinutes)
            putExtra(EXTRA_HOUR, hour)
            putExtra(EXTRA_MINUTE, minute)
        }

        return PendingIntent.getBroadcast(
            context,
            ALERT_REQUEST_CODE,
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
    }

    private fun createNotificationChannel(context: Context) {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.O) {
            return
        }

        val notificationManager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        val channel = NotificationChannel(
            CHANNEL_ID,
            context.getString(com.nativephp.mobile.R.string.under_hours_channel_name),
            NotificationManager.IMPORTANCE_HIGH
        ).apply {
            description = context.getString(com.nativephp.mobile.R.string.under_hours_channel_description)
        }

        notificationManager.createNotificationChannel(channel)
    }

    private fun resolveZoneId(timezone: String): ZoneId {
        return try {
            ZoneId.of(timezone)
        } catch (_: Exception) {
            ZoneId.systemDefault()
        }
    }

    private fun persistConfiguration(
        context: Context,
        timezone: String,
        profileName: String,
        requiredMinutes: Int,
        todayTotalMinutes: Int,
        hour: Int,
        minute: Int,
        snapshotDate: String
    ) {
        context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .edit()
            .putString(PREF_TIMEZONE, timezone)
            .putString(PREF_PROFILE_NAME, profileName)
            .putInt(PREF_REQUIRED_MINUTES, requiredMinutes.coerceIn(60, 960))
            .putInt(PREF_TODAY_TOTAL_MINUTES, todayTotalMinutes.coerceAtLeast(0))
            .putInt(PREF_HOUR, hour.coerceIn(0, 23))
            .putInt(PREF_MINUTE, minute.coerceIn(0, 59))
            .putString(PREF_SNAPSHOT_DATE, snapshotDate)
            .apply()
    }

    private fun clearConfiguration(context: Context) {
        context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .edit()
            .clear()
            .apply()
    }
}
