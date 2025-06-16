import { HttpInterceptorFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  // Simplemente clonamos la solicitud agregando withCredentials: true
  const authReq = req.clone({
    withCredentials: true
  });

  return next(authReq);
};
