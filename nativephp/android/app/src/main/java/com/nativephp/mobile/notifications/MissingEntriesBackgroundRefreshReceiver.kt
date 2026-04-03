package com.nativephp.mobile.notifications

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent

class MissingEntriesBackgroundRefreshReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent?) {
        when (intent?.action) {
            Intent.ACTION_BOOT_COMPLETED,
            Intent.ACTION_MY_PACKAGE_REPLACED,
            Intent.ACTION_TIMEZONE_CHANGED,
            Intent.ACTION_TIME_CHANGED -> {
                MissingEntriesReminderScheduler.refreshFromStorage(context)
            }
        }
    }
}
