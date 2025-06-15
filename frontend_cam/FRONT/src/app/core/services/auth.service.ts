import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, switchMap, tap } from 'rxjs';
import { LoginRequest, AuthUser } from '../models/login.model';
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private baseUrl = environment.URL_SERVICIOS;
    private csrfUrl = `http://127.0.0.1:8000/sanctum/csrf-cookie`;

  private loginUrl = `${this.baseUrl}/login`;
  private logoutUrl = `${this.baseUrl}/logout`;
  private userUrl = `${this.baseUrl}/user`;

  constructor(private http: HttpClient) {}

  /**
   * Realiza el login usando Sanctum:
   * 1. Obtiene cookie CSRF.
   * 2. Envia las credenciales.
   * 3. Obtiene el usuario autenticado.
   */
  login(credentials: LoginRequest): Observable<AuthUser> {
    return this.http.get(this.csrfUrl, { withCredentials: true }).pipe(
      switchMap(() =>
        this.http.post(this.loginUrl, credentials, { withCredentials: true })
      ),
      switchMap(() =>
        this.http.get<AuthUser>(this.userUrl, { withCredentials: true })
      ),
      tap(user => this.setCurrentUser(user))
    );
  }

  /**
   * Cierra sesión en Laravel y limpia el almacenamiento local.
   */
  logout(): Observable<any> {
    return this.http.post(this.logoutUrl, {}, { withCredentials: true }).pipe(
      tap(() => localStorage.removeItem('user'))
    );
  }

  /**
   * Guarda el usuario autenticado en localStorage.
   */
  setCurrentUser(user: AuthUser): void {
    localStorage.setItem('user', JSON.stringify(user));
  }

  /**
   * Devuelve el usuario autenticado desde localStorage.
   */
  getCurrentUser(): AuthUser | null {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  /**
   * Verifica si hay usuario autenticado.
   */
  isAuthenticated(): boolean {
    return !!this.getCurrentUser();
  }
  getToken(): string | null {
  return localStorage.getItem('access_token');
}

}
