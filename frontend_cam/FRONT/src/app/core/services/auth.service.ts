import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { LoginRequest, UsuarioLoginResponse } from '../models/login.model';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  // private apiUrl = 'http://localhost:8000/api/login';
  private apiUrl = `${environment.URL_SERVICIOS}/login`;

  constructor(private http: HttpClient) {}

  login(credentials: LoginRequest): Observable<UsuarioLoginResponse> {
    console.log('Enviando credenciales al backend:', credentials); // <--- LOG lo que vas a enviar

    return this.http.post<UsuarioLoginResponse>(this.apiUrl, credentials).pipe(
      tap(response => {
        console.log('Respuesta recibida del backend:', response); // <--- LOG la respuesta recibida
        // Aquí es donde obtienes el token: response.token
      },
      error => {
        console.error('Error en la solicitud de login:', error); // <--- LOG cualquier error
      })
    );
  }
  getToken(): string | null {
    return localStorage.getItem('access_token');
  }
  setCurrentUser(user: UsuarioLoginResponse): void {
    localStorage.setItem('user', JSON.stringify(user));
  }

  getCurrentUser(): UsuarioLoginResponse | null {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  logout(): void {
    localStorage.removeItem('user');
  }
}
