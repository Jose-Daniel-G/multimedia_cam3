import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError, Observable, throwError } from 'rxjs';
import {
  Role,
  Permission,
  CreateRolePayload, // Crucial: Import CreateRolePayload
  UpdateRolePayload, // Crucial: Import UpdateRolePayload
  ApiResponse,
  SuccessMessageResponse
} from '../models/role.model';

import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root' // Este servicio será un singleton y estará disponible en toda la aplicación
})
export class RoleService {
  private apiUrl = `${environment.URL_SERVICIOS}`; // Ajusta esta URL a tu endpoint de roles en Laravel
  private rolesEndpoint = `${this.apiUrl}/roles`; // Specific endpoint for roles
  private permissionsEndpoint = `${this.apiUrl}/permissions`; // Ajusta esta URL a tu endpoint de roles en Laravel

  constructor(private http: HttpClient) {}

  private getAuthHeaders(): HttpHeaders {
    // Removed direct 'Authorization' header as it should be handled by authInterceptor
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      // The authInterceptor will add 'Authorization: Bearer <token>' if needed
    });
  }

    private handleError(error: HttpErrorResponse) {
    let errorMessage = 'An unknown error occurred!';
    if (error.error instanceof ErrorEvent) {
      errorMessage = `Error: ${error.error.message}`;
    } else {
      console.error(
        `Backend returned code ${error.status}, ` +
        `body was: ${JSON.stringify(error.error)}`);
      errorMessage = `Error ${error.status}: ${error.error?.message || error.statusText}`;
      if (error.status === 422 && error.error.errors) {
        const validationErrors = Object.values(error.error.errors)
          .flat()
          .join('; ');
        errorMessage += `\nValidation Details: ${validationErrors}`;
      }
    }
    return throwError(() => new Error(errorMessage));
  }

  getRoles(page: number = 1, perPage: number = 10): Observable<ApiResponse<Role[]>> {
    return this.http.get<ApiResponse<Role[]>>(`${this.rolesEndpoint}?page=${page}&per_page=${perPage}`, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }

  getRole(id: number): Observable<Role> {
    return this.http.get<Role>(`${this.rolesEndpoint}/${id}`, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }
  getPermissions(): Observable<ApiResponse<Permission[]>> {
    return this.http.get<ApiResponse<Permission[]>>(`${this.permissionsEndpoint}`, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }
  /**
   * Crea un nuevo rol.
   * @param role Los datos del nuevo rol.
   * @returns Un Observable que emite el rol creado.
   */
  createRole(payload: CreateRolePayload): Observable<SuccessMessageResponse> {
    return this.http.post<SuccessMessageResponse>(this.rolesEndpoint, payload, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }

  /**
   * Actualiza un rol existente.
   * @param id El ID del rol a actualizar.
   * @param role Los datos actualizados del rol.
   * @returns Un Observable que emite el rol actualizado.
   */
  updateRole(id: number, payload: UpdateRolePayload): Observable<SuccessMessageResponse> {
    return this.http.put<SuccessMessageResponse>(`${this.rolesEndpoint}/${id}`, payload, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }

  /**
   * Elimina un rol.
   * @param id El ID del rol a eliminar.
   * @returns Un Observable que emite la respuesta de la eliminación.
   */
  deleteRole(id: number): Observable<SuccessMessageResponse> {
    return this.http.delete<SuccessMessageResponse>(`${this.rolesEndpoint}/${id}`, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }
  
}
