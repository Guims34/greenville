import * as Sentry from '@sentry/react';
import { Integrations } from '@sentry/tracing';

export const initializeMonitoring = () => {
  if (import.meta.env.PROD) {
    Sentry.init({
      dsn: import.meta.env.VITE_SENTRY_DSN,
      integrations: [new Integrations.BrowserTracing()],
      tracesSampleRate: 1.0,
      environment: import.meta.env.MODE,
      beforeSend(event) {
        // Nettoyer les données sensibles
        if (event.user) {
          delete event.user.ip_address;
          delete event.user.email;
        }
        return event;
      }
    });
  }
};