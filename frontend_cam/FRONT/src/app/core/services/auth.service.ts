import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, switchMap, tap, catchError, throwError } from 'rxjs'; // Asegúrate de importar todos estos operadores
import { LoginRequest, AuthUser } from '../models/login.model'; // Asumo AuthUser para el tipo de usuario devuelto
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private baseUrl = environment.URL_SERVICIOS; // Ejemplo: http://127.0.0.1:8000/api

  // La URL para obtener la cookie CSRF de Sanctum.
  // Es importante que esta URL NO incluya '/api' si tu Laravel está configurado para servir
  // 'sanctum/csrf-cookie' directamente desde la raíz.
  // Si environment.URL_SERVICIOS es "http://127.0.0.1:8000/api",
  // .replace('/api', '') lo convierte en "http://127.0.0.1:8000".
  private csrfUrl = `${this.baseUrl.replace('/api', '')}/sanctum/csrf-cookie`;

  private loginUrl = `${this.baseUrl}/login`;
  private logoutUrl = `${this.baseUrl}/logout`;
  private userUrl = `${this.baseUrl}/user`; // Endpoint para obtener los datos del usuario autenticado

  constructor(private http: HttpClient) {}

  /**
   * Realiza el login usando Laravel Sanctum.
   * Flujo completo:
   * 1. Petición GET para obtener la cookie CSRF.
   * 2. Envía las credenciales al endpoint de login (POST /api/login).
   * Laravel devuelve el access_token si las credenciales son válidas.
   * 3. Guarda el access_token y los datos del usuario en localStorage.
   * 4. Realiza una petición GET a /api/user para obtener los datos completos del usuario (protegida por el token).
   * @param credentials Las credenciales de login (email y password).
   * @returns Un Observable que emite los datos del usuario autenticado.
   */
  login(credentials: LoginRequest): Observable<AuthUser> {
    console.log('AuthService - Iniciando login para:', credentials.email);
    console.log('AuthService - CSRF URL:', this.csrfUrl);
    console.log('AuthService - Login URL:', this.loginUrl);

    // Paso 1: Obtener la cookie CSRF. Es vital para las peticiones POST/PUT/DELETE en Laravel.
    return this.http.get(this.csrfUrl, { withCredentials: true }).pipe(
      tap(() => console.log('AuthService - CSRF cookie request successful.')),
      // Paso 2: Encadenar la petición POST de login. Se ejecuta solo si la petición CSRF fue exitosa.
      switchMap(() => {
        console.log('AuthService - Sending login POST request...');
        // Esperamos que Laravel devuelva un objeto con 'message', 'user' y 'access_token'.
        return this.http.post<{ message: string; user: AuthUser; access_token: string }>( // <--- Define la estructura de respuesta esperada
          this.loginUrl,
          credentials,
          { withCredentials: true } // ¡CRÍTICO! Para enviar la cookie XSRF-TOKEN en esta petición
        );
      }),
      // Paso 3: Usar 'tap' para manejar la respuesta del login y guardar el token.
      tap(response => {
        if (response.access_token && response.user) {
          localStorage.setItem('access_token', response.access_token); // <-- ¡GUARDAR EL TOKEN DE ACCESO!
          this.setCurrentUser(response.user); // Guarda los datos del usuario
          console.log('AuthService - Login POST successful. Token and user data saved.');
          console.log('AuthService - Token guardado:', response.access_token);
        } else {
          console.error('AuthService - Login successful but server response missing access_token or user data:', response);
          throw new Error('Invalid login response from server: access_token or user data missing.');
        }
      }),
      // Paso 4: Encadenar la petición GET /api/user para obtener los datos completos del usuario.
      // El interceptor ya tendrá el token en localStorage y lo adjuntará automáticamente.
      switchMap(() => {
        const tokenForUserRequest = this.getToken();
        console.log('AuthService - Token before /user request:', tokenForUserRequest ? 'Present' : 'Absent');
        return this.http.get<AuthUser>(this.userUrl, { withCredentials: true });
      }),
      tap(user => {
        console.log('AuthService - User data retrieved from /user endpoint:', user);
      }),
      catchError(error => {
        console.error('AuthService - Error in login flow:', error);
        // Limpiar cualquier estado de autenticación parcial en caso de error.
        localStorage.removeItem('user');
        localStorage.removeItem('access_token');
        return throwError(() => error); // Propaga el error
      })
    );
  }

  /**
   * Cierra sesión en el backend de Laravel y limpia el almacenamiento local.
   * @returns Un Observable que emite la respuesta de la petición de logout.
   */
  logout(): Observable<any> {
    console.log('AuthService - Initiating logout.');
    // La petición de logout. El interceptor debe adjuntar el token aquí.
    return this.http.post(this.logoutUrl, {}, { withCredentials: true }).pipe(
      tap(() => {
        localStorage.removeItem('user');
        localStorage.removeItem('access_token'); // ¡Limpiar también el token de acceso!
        console.log('AuthService - Logout successful. Token and user cleared from localStorage.');
      }),
      catchError(error => {
        console.error('AuthService - Error during logout:', error);
        // Limpiar localmente incluso si el backend falla, para asegurar un estado limpio en el frontend
        localStorage.removeItem('user');
        localStorage.removeItem('access_token');
        return throwError(() => error);
      })
    );
  }

  /**
   * Guarda los datos del usuario autenticado en localStorage.
   * @param user El objeto AuthUser (o UsuarioLoginResponse si contiene los mismos campos principales).
   */
  setCurrentUser(user: AuthUser): void {
    localStorage.setItem('user', JSON.stringify(user));
  }

  /**
   * Recupera los datos del usuario actual desde localStorage.
   * @returns El objeto AuthUser si está presente, o null si no hay usuario guardado.
   */
  getCurrentUser(): AuthUser | null {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  /**
   * Recupera el token de acceso (Bearer Token) del localStorage.
   * Este método es crucial para que el interceptor pueda adjuntar el token a las solicitudes.
   * @returns El token de acceso como string, o null si no está presente.
   */
  getToken(): string | null {
    return localStorage.getItem('access_token');
  }

  /**
   * Verifica si el usuario está actualmente autenticado.
   * Considera que un usuario está autenticado si hay datos de usuario Y un token de acceso guardados.
   * @returns True si el usuario está autenticado, false en caso contrario.
   */
  isAuthenticated(): boolean {
    return !!this.getCurrentUser() && !!this.getToken(); // ¡CRÍTICO! Verifica ambos.
  }
  hasPermission(permission: string): boolean {
    const user = this.getCurrentUser();
    // Verifica si el usuario existe y si su array de permisos incluye el permiso dado.
    // Usamos el operador de encadenamiento opcional (?) para evitar errores si 'permissions' es undefined.
    return user?.permissions?.includes(permission) ?? false;
  }
}
