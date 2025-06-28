import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError, Observable, throwError, map } from 'rxjs'; // Import 'map' operator
import {
  Role,
  Permission,
  CreateRolePayload,
  UpdateRolePayload,
  PaginationData, // Use the updated ApiResponse
  SuccessMessageResponse,
  ApiResponse
} from '../models/role.model'; // Correct import path

import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class RoleService {
  private apiUrl = `${environment.URL_SERVICIOS}`;
  private rolesEndpoint = `${this.apiUrl}/roles`;
  private permissionsEndpoint = `${this.apiUrl}/permissions`;

  constructor(private http: HttpClient) {}

  private getAuthHeaders(): HttpHeaders {
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
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
      if (error.error?.errors) {
        const validationErrors = Object.values(error.error.errors)
          .flat()
          .join('; ');
        errorMessage += `\nValidation Details: ${validationErrors}`;
      }
    }
    return throwError(() => new Error(errorMessage));
  }

  // getRoles now returns ApiResponse<Role[]> where ApiResponse has pagination data directly
  getRoles(page: number = 1, perPage: number = 10): Observable<PaginationData<Role[]>> {
    // Expecting the full PaginationData structure that contains PaginationData<Role[]>
    return this.http.get<PaginationData<Role[]>>(`${this.rolesEndpoint}?page=${page}&per_page=${perPage}`, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }

  // getRole for a single role
  getRole(id: number): Observable<Role> {
    // Assuming this endpoint returns a single Role object directly, NOT wrapped in ApiResponse
    return this.http.get<Role>(`${this.rolesEndpoint}/${id}`, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }

  // getPermissions now returns ApiResponse<Permission[]> where ApiResponse has pagination data directly
  // This method's return type matches the ApiResponse interface now that it directly includes pagination
  getPermissions(): Observable<ApiResponse<Permission[]>> {
    // Expecting the full ApiResponse structure that contains PaginationData<Permission[]>
    return this.http.get<ApiResponse<Permission[]>>(`${this.permissionsEndpoint}`, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }

  /**
   * Crea un nuevo rol.
   */
  createRole(payload: CreateRolePayload): Observable<SuccessMessageResponse> {
    return this.http.post<SuccessMessageResponse>(this.rolesEndpoint, payload, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }

  /**
   * Actualiza un rol existente.
   */
  updateRole(id: number, payload: UpdateRolePayload): Observable<SuccessMessageResponse> {
    return this.http.put<SuccessMessageResponse>(`${this.rolesEndpoint}/${id}`, payload, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }

  /**
   * Elimina un rol.
   */
  deleteRole(id: number): Observable<SuccessMessageResponse> {
    return this.http.delete<SuccessMessageResponse>(`${this.rolesEndpoint}/${id}`, { headers: this.getAuthHeaders() })
      .pipe(
        catchError(this.handleError)
      );
  }
}