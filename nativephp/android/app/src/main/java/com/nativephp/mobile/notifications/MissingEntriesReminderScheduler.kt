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

object MissingEntriesReminderScheduler {
    private const val REMINDER_REQUEST_CODE = 7401
    private const val PREFS_NAME = "hourledger_missing_entries_refresh"
    private const val PREF_TIMEZONE = "timezone"
    private const val PREF_PROFILE_NAME = "profile_name"
    private const val PREF_HOUR = "hour"
    private const val PREF_MINUTE = "minute"
    private const val PREF_SKIP_TODAY = "skip_today"
    const val CHANNEL_ID = "hourledger_missing_entries"
    const val ACTION_REMINDER = "com.nativephp.mobile.action.MISSING_ENTRIES_REMINDER"
    const val EXTRA_PROFILE_NAME = "profile_name"
    const val EXTRA_TIMEZONE = "timezone"
    const val EXTRA_HOUR = "hour"
    const val EXTRA_MINUTE = "minute"

    fun sync(
        context: Context,
        enabled: Boolean,
        timezone: String,
        profileName: String,
        hour: Int,
        minute: Int,
        skipToday: Boolean
    ) {
        if (!enabled) {
            cancel(context)
            return
        }

        persistConfiguration(
            context = context,
            timezone = timezone,
            profileName = profileName,
            hour = hour,
            minute = minute,
            skipToday = skipToday
        )

        createNotificationChannel(context)

        val now = ZonedDateTime.now(resolveZoneId(timezone))
        var target = now
            .withHour(hour.coerceIn(0, 23))
            .withMinute(minute.coerceIn(0, 59))
            .withSecond(0)
            .withNano(0)

        if (skipToday || !target.isAfter(now)) {
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
            hour,
            minute
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
        val pendingIntent = buildPendingIntent(context, "HourLedger", "UTC", 9, 0)
        alarmManager.cancel(pendingIntent)

        val notificationManager = context.getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        notificationManager.cancel(MissingEntriesReminderReceiver.NOTIFICATION_ID)
    }

    fun refreshFromStorage(context: Context): Boolean {
        val prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)

        if (!prefs.contains(PREF_TIMEZONE) || !prefs.contains(PREF_PROFILE_NAME)) {
            return false
        }

        val timezone = prefs.getString(PREF_TIMEZONE, "UTC") ?: "UTC"
        val profileName = prefs.getString(PREF_PROFILE_NAME, "HourLedger") ?: "HourLedger"
        val hour = prefs.getInt(PREF_HOUR, 9)
        val minute = prefs.getInt(PREF_MINUTE, 0)
        val skipToday = prefs.getBoolean(PREF_SKIP_TODAY, false)

        sync(
            context = context,
            enabled = true,
            timezone = timezone,
            profileName = profileName,
            hour = hour,
            minute = minute,
            skipToday = skipToday
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
        hour: Int,
        minute: Int
    ): PendingIntent {
        val intent = Intent(context, MissingEntriesReminderReceiver::class.java).apply {
            action = ACTION_REMINDER
            putExtra(EXTRA_PROFILE_NAME, profileName)
            putExtra(EXTRA_TIMEZONE, timezone)
            putExtra(EXTRA_HOUR, hour)
            putExtra(EXTRA_MINUTE, minute)
        }

        return PendingIntent.getBroadcast(
            context,
            REMINDER_REQUEST_CODE,
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
            context.getString(com.nativephp.mobile.R.string.missing_entries_channel_name),
            NotificationManager.IMPORTANCE_DEFAULT
        ).apply {
            description = context.getString(com.nativephp.mobile.R.string.missing_entries_channel_description)
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
        hour: Int,
        minute: Int,
        skipToday: Boolean
    ) {
        context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .edit()
            .putString(PREF_TIMEZONE, timezone)
            .putString(PREF_PROFILE_NAME, profileName)
            .putInt(PREF_HOUR, hour.coerceIn(0, 23))
            .putInt(PREF_MINUTE, minute.coerceIn(0, 59))
            .putBoolean(PREF_SKIP_TODAY, skipToday)
            .apply()
    }

    private fun clearConfiguration(context: Context) {
        context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .edit()
            .clear()
            .apply()
    }
}
