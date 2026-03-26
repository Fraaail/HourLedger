package com.nativephp.mobile.notifications

import android.app.PendingIntent
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import com.nativephp.mobile.R
import com.nativephp.mobile.ui.MainActivity

class MissingEntriesReminderReceiver : BroadcastReceiver() {
    companion object {
        const val NOTIFICATION_ID = 7401
    }

    override fun onReceive(context: Context, intent: Intent?) {
        val profileName = intent?.getStringExtra(MissingEntriesReminderScheduler.EXTRA_PROFILE_NAME)
            ?: "HourLedger"
        val timezone = intent?.getStringExtra(MissingEntriesReminderScheduler.EXTRA_TIMEZONE)
            ?: "UTC"
        val hour = intent?.getIntExtra(MissingEntriesReminderScheduler.EXTRA_HOUR, 9) ?: 9
        val minute = intent?.getIntExtra(MissingEntriesReminderScheduler.EXTRA_MINUTE, 0) ?: 0

        val openAppIntent = Intent(context, MainActivity::class.java)
        val openAppPendingIntent = PendingIntent.getActivity(
            context,
            0,
            openAppIntent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        if (MissingEntriesReminderScheduler.canPostNotifications(context)) {
            val title = context.getString(R.string.missing_entries_notification_title)
            val body = context.getString(R.string.missing_entries_notification_body, profileName)

            val notification = NotificationCompat.Builder(context, MissingEntriesReminderScheduler.CHANNEL_ID)
                .setSmallIcon(R.mipmap.ic_launcher)
                .setContentTitle(title)
                .setContentText(body)
                .setStyle(NotificationCompat.BigTextStyle().bigText(body))
                .setAutoCancel(true)
                .setContentIntent(openAppPendingIntent)
                .setPriority(NotificationCompat.PRIORITY_DEFAULT)
                .build()

            NotificationManagerCompat.from(context).notify(NOTIFICATION_ID, notification)
        }

        // Reschedule for the next eligible weekday reminder.
        MissingEntriesReminderScheduler.sync(
            context = context,
            enabled = true,
            timezone = timezone,
            profileName = profileName,
            hour = hour,
            minute = minute,
            skipToday = true
        )
    }
}
