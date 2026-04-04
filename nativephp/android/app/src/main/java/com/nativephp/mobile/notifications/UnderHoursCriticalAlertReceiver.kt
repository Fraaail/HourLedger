package com.nativephp.mobile.notifications

import android.app.PendingIntent
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import com.nativephp.mobile.R
import com.nativephp.mobile.ui.MainActivity
import java.util.Locale

class UnderHoursCriticalAlertReceiver : BroadcastReceiver() {
    companion object {
        const val NOTIFICATION_ID = 7501
    }

    override fun onReceive(context: Context, intent: Intent?) {
        val profileName = intent?.getStringExtra(UnderHoursCriticalAlertScheduler.EXTRA_PROFILE_NAME)
            ?: "HourLedger"
        val timezone = intent?.getStringExtra(UnderHoursCriticalAlertScheduler.EXTRA_TIMEZONE)
            ?: "UTC"
        val requiredMinutes = intent?.getIntExtra(UnderHoursCriticalAlertScheduler.EXTRA_REQUIRED_MINUTES, 480) ?: 480
        val todayTotalMinutes = intent?.getIntExtra(UnderHoursCriticalAlertScheduler.EXTRA_TODAY_TOTAL_MINUTES, 0) ?: 0
        val hour = intent?.getIntExtra(UnderHoursCriticalAlertScheduler.EXTRA_HOUR, 18) ?: 18
        val minute = intent?.getIntExtra(UnderHoursCriticalAlertScheduler.EXTRA_MINUTE, 0) ?: 0

        val openAppIntent = Intent(context, MainActivity::class.java)
        val openAppPendingIntent = PendingIntent.getActivity(
            context,
            0,
            openAppIntent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        if (UnderHoursCriticalAlertScheduler.canPostNotifications(context)) {
            val title = context.getString(R.string.under_hours_notification_title)
            val body = context.getString(
                R.string.under_hours_notification_body,
                profileName,
                formatHours(todayTotalMinutes),
                formatHours(requiredMinutes)
            )

            val notification = NotificationCompat.Builder(context, UnderHoursCriticalAlertScheduler.CHANNEL_ID)
                .setSmallIcon(R.mipmap.ic_launcher)
                .setContentTitle(title)
                .setContentText(body)
                .setStyle(NotificationCompat.BigTextStyle().bigText(body))
                .setAutoCancel(true)
                .setContentIntent(openAppPendingIntent)
                .setPriority(NotificationCompat.PRIORITY_HIGH)
                .build()

            NotificationManagerCompat.from(context).notify(NOTIFICATION_ID, notification)
        }

        // After firing, move scheduling to the next weekday and reset today's minutes snapshot.
        UnderHoursCriticalAlertScheduler.sync(
            context = context,
            enabled = true,
            timezone = timezone,
            profileName = profileName,
            requiredMinutes = requiredMinutes,
            todayTotalMinutes = 0,
            hour = hour,
            minute = minute
        )
    }

    private fun formatHours(minutes: Int): String {
        return String.format(Locale.US, "%.1f", minutes.coerceAtLeast(0) / 60.0)
    }
}
