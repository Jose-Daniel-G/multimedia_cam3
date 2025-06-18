import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service'; // Asegúrate de que esta ruta sea correcta

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const authService = inject(AuthService);
  const token = authService.getToken(); // Obtiene el token desde el AuthService

  console.log('Interceptor - Procesando URL:', req.url);
  console.log('Interceptor - Token disponible (desde AuthService):', !!token);
  // Excluye la petición de CSRF cookie y la petición de login inicial.
  const isExcludedUrl = req.url.includes('/sanctum/csrf-cookie') || req.url.includes('/login');
  console.log('Interceptor - ¿Es URL excluida (CSRF/Login)?:', isExcludedUrl);

  // Si hay un token Y la URL NO es una de las URLs excluidas, adjunta el token.
  if (token && !isExcludedUrl) {
    console.log('Interceptor - **** ADJUNTANDO TOKEN BEARER A ESTA URL: ****', req.url); // <-- ¡ESTE LOG ES CLAVE!
    // Clona la solicitud y añade el encabezado de autorización.
    return next(req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    }));
  }
  console.log('Interceptor - NO se adjunta token a:', req.url); // <-- Si esto aparece para /api/user, es el problema.
  return next(req);
};
