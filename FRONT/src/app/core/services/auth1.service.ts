import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, switchMap, tap, catchError, throwError } from 'rxjs';
import { LoginRequest, AuthUser } from '../models/login.model';
import { environment } from '../../../environments/environment';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private baseUrl = environment.URL_SERVICIOS;
  private csrfUrl = `${this.baseUrl.replace('/api', '')}/sanctum/csrf-cookie`;
  private loginUrl = `${this.baseUrl}/login`;
  private logoutUrl = `${this.baseUrl}/logout`;
  private userUrl = `${this.baseUrl}/user`;

  constructor(private http: HttpClient) {}

  login(credentials: LoginRequest): Observable<AuthUser> {
    console.log('AuthService - Iniciando login para:', credentials.email);
    console.log('AuthService - CSRF URL:', this.csrfUrl);
    console.log('AuthService - Login URL:', this.loginUrl);

    return this.http.get(this.csrfUrl, { withCredentials: true }).pipe(
      tap(() => console.log('AuthService - CSRF cookie request successful.')),
      switchMap(() => {
        console.log('AuthService - Sending login POST request...');
        return this.http.post<{ message: string; user: AuthUser; access_token: string }>(
          this.loginUrl,
          credentials,
          { withCredentials: true }
        );
      }),
      tap(response => {
        if (response.access_token && response.user) {
          localStorage.setItem('access_token', response.access_token);
          this.setCurrentUser(response.user); // <-- Aquí se guarda el objeto de usuario
          console.log('AuthService - Login POST successful. Token and user data saved.');
          console.log('AuthService - Token guardado:', response.access_token);
          // *** NUEVO LOG: Inspecciona la respuesta del usuario del login ***
          console.log('AuthService - User object from Login response:', response.user);
          if (response.user.roles) {
            console.log('AuthService - Roles found in Login user object:', response.user.roles);
          } else {
            console.log('AuthService - NO ROLES property found in Login user object!');
          }
        } else {
          console.error('AuthService - Login successful but server response missing access_token or user data:', response);
          throw new Error('Invalid login response from server: access_token or user data missing.');
        }
      }),
      switchMap(() => {
        const tokenForUserRequest = this.getToken();
        console.log('AuthService - Token before /user request:', tokenForUserRequest ? 'Present' : 'Absent');
        return this.http.get<AuthUser>(this.userUrl, { withCredentials: true });
      }),
      tap(user => {
        console.log('AuthService - User data retrieved from /user endpoint:', user);
        // Puedes llamar a setCurrentUser aquí también si /user endpoint retorna el objeto completo
        // o si es la fuente definitiva de los datos del usuario después del login.
        // this.setCurrentUser(user); // Descomenta si necesitas actualizar el usuario después de esta llamada
        // *** NUEVO LOG: Inspecciona la respuesta del usuario del /user endpoint ***
        console.log('AuthService - User object from /user endpoint:', user);
        if (user.roles) {
          console.log('AuthService - Roles found in /user endpoint response:', user.roles);
        } else {
          console.log('AuthService - NO ROLES property found in /user endpoint response!');
        }
      }),
      catchError(error => {
        console.error('AuthService - Error in login flow:', error);
        localStorage.removeItem('user');
        localStorage.removeItem('access_token');
        return throwError(() => error);
      })
    );
  }

  logout(): Observable<any> {
    console.log('AuthService - Initiating logout.');
    return this.http.post(this.logoutUrl, {}, { withCredentials: true }).pipe(
      tap(() => {
        localStorage.removeItem('user');
        localStorage.removeItem('access_token');
        console.log('AuthService - Logout successful. Token and user cleared from localStorage.');
      }),
      catchError(error => {
        console.error('AuthService - Error during logout:', error);
        localStorage.removeItem('user');
        localStorage.removeItem('access_token');
        return throwError(() => error);
      })
    );
  }

  setCurrentUser(user: AuthUser): void {
    console.log('AuthService - Guardando usuario en localStorage:', user);
    localStorage.setItem('user', JSON.stringify(user));
  }

  getCurrentUser(): AuthUser | null {
    const userJson = localStorage.getItem('user');
    const parsedUser = userJson ? JSON.parse(userJson) : null;
    console.log('AuthService - Recuperando usuario de localStorage. Objeto crudo:', userJson);
    console.log('AuthService - Recuperando usuario de localStorage. Objeto parseado:', parsedUser);
    return parsedUser;
  }

  getToken(): string | null {
    return localStorage.getItem('access_token');
  }

  isAuthenticated(): boolean {
    const authStatus = !!this.getCurrentUser() && !!this.getToken();
    console.log('AuthService - isAuthenticated:', authStatus);
    return authStatus;
  }

  hasPermission(permissionToCheck: string): boolean {
    const user = this.getCurrentUser();
    console.log(`[AuthService] hasPermission('${permissionToCheck}'): Intentando verificar permiso.`);
    // *** NUEVO LOG: Inspeccionar el objeto de usuario JUSTO ANTES de la validación de roles ***
    console.log('[AuthService] hasPermission: Objeto de usuario cargado para verificación:', user);

    if (!user) {
      console.log(`[AuthService] hasPermission('${permissionToCheck}'): Usuario es null. Resultado: false`);
      return false;
    }
    
    // *** NUEVO LOG: Inspeccionar la propiedad 'roles' del usuario ***
    if (!user.roles) {
      console.log(`[AuthService] hasPermission('${permissionToCheck}'): user.roles es undefined o null. Resultado: false`);
      return false;
    }

    if (user.roles.length === 0) {
      console.log(`[AuthService] hasPermission('${permissionToCheck}'): user.roles es un array vacío. Resultado: false`);
      return false;
    }

    for (const role of user.roles) {
      console.log(`[AuthService] Verificando rol: '${role.name}' (ID: ${role.id})`);
      // *** NUEVO LOG: Inspeccionar la propiedad 'permissions' del rol ***
      if (!role.permissions) {
        console.log(`[AuthService] - Rol '${role.name}' no tiene la propiedad 'permissions'.`);
        continue; // Pasar al siguiente rol si no tiene la propiedad de permisos
      }
      
      if (role.permissions.length === 0) {
        console.log(`[AuthService] - Rol '${role.name}' tiene un array de permisos vacío.`);
        continue; // Pasar al siguiente rol si no tiene permisos
      }

      for (const perm of role.permissions) {
        console.log(`[AuthService] - Permiso del rol '${role.name}': '${perm.name}' (ID: ${perm.id})`);
        if (perm.name === permissionToCheck) {
          console.log(`[AuthService] hasPermission('${permissionToCheck}'): Permiso ENCONTRADO en rol '${role.name}'. Resultado: true`);
          return true;
        }
      }
    }

    console.log(`[AuthService] hasPermission('${permissionToCheck}'): Permiso NO ENCONTRADO en ningún rol del usuario. Resultado: false`);
    return false;
  }
}
