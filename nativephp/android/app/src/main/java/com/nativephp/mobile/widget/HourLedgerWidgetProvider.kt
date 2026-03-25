package com.nativephp.mobile.widget

import android.app.PendingIntent
import android.appwidget.AppWidgetManager
import android.appwidget.AppWidgetProvider
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.widget.RemoteViews
import com.nativephp.mobile.R
import com.nativephp.mobile.ui.MainActivity

class HourLedgerWidgetProvider : AppWidgetProvider() {

    override fun onUpdate(context: Context, appWidgetManager: AppWidgetManager, appWidgetIds: IntArray) {
        super.onUpdate(context, appWidgetManager, appWidgetIds)
        appWidgetIds.forEach { appWidgetId ->
            updateWidget(context, appWidgetManager, appWidgetId)
        }
    }

    override fun onAppWidgetOptionsChanged(
        context: Context,
        appWidgetManager: AppWidgetManager,
        appWidgetId: Int,
        newOptions: Bundle
    ) {
        super.onAppWidgetOptionsChanged(context, appWidgetManager, appWidgetId, newOptions)
        updateWidget(context, appWidgetManager, appWidgetId)
    }

    companion object {
        fun refreshAll(context: Context) {
            val appWidgetManager = AppWidgetManager.getInstance(context)
            val componentName = ComponentName(context, HourLedgerWidgetProvider::class.java)
            val widgetIds = appWidgetManager.getAppWidgetIds(componentName)

            if (widgetIds.isEmpty()) {
                return
            }

            widgetIds.forEach { appWidgetId ->
                updateWidget(context, appWidgetManager, appWidgetId)
            }
        }

        private fun updateWidget(context: Context, manager: AppWidgetManager, widgetId: Int) {
            val snapshot = HomeWidgetStore.snapshot(context)
            val options = manager.getAppWidgetOptions(widgetId)
            val minWidth = options.getInt(AppWidgetManager.OPTION_APPWIDGET_MIN_WIDTH)
            val layoutId = if (minWidth >= 250) {
                R.layout.hourledger_widget_medium
            } else {
                R.layout.hourledger_widget_small
            }

            val views = RemoteViews(context.packageName, layoutId)

            views.setTextViewText(R.id.widget_profile_name, snapshot.profileName)
            views.setTextViewText(R.id.widget_status_label, snapshot.statusLabel)
            views.setTextViewText(R.id.widget_total_hours, context.getString(R.string.widget_total_hours_value, snapshot.totalHours))
            views.setTextViewText(R.id.widget_total_days, context.getString(R.string.widget_total_days_value, snapshot.totalDays))
            views.setTextViewText(R.id.widget_clocked_in, context.getString(R.string.widget_clocked_in_value, snapshot.clockedInAt))

            val launchIntent = Intent(context, MainActivity::class.java)
            val pendingIntent = PendingIntent.getActivity(
                context,
                0,
                launchIntent,
                PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
            )
            views.setOnClickPendingIntent(R.id.widget_root, pendingIntent)

            manager.updateAppWidget(widgetId, views)
        }
    }
}
