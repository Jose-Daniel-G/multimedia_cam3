// src/app/core/services/role.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Role } from '../models/role.model';
import { Permission } from '../models/permission.model'; // Assuming you have this
import { environment } from '../../../environments/environment'; // Adjust path as needed

@Injectable({
  providedIn: 'root',
})
export class RoleService {
  private apiUrl = `${environment.URL_SERVICIOS}/roles`; // Adjust API endpoint as per your backend

  constructor(private http: HttpClient) {}

  /**
   * Fetches all roles from the API.
   * @returns An Observable of an array of Role objects.
   */
  getAllRoles(): Observable<Role[]> {
    return this.http.get<Role[]>(this.apiUrl);
  }

  /**
   * Fetches a single role by its ID.
   * @param id The ID of the role.
   * @returns An Observable of a single Role object.
   */
  getRoleById(id: number): Observable<Role> {
    return this.http.get<Role>(`${this.apiUrl}/${id}`);
  }

  /**
   * Creates a new role.
   * @param role The Role object to create.
   * @returns An Observable of the created Role object.
   */
  createRole(role: Role): Observable<Role> {
    return this.http.post<Role>(this.apiUrl, role);
  }

  /**
   * Updates an existing role.
   * @param id The ID of the role to update.
   * @param role The updated Role object.
   * @returns An Observable of the updated Role object.
   */
  updateRole(id: number, role: Role): Observable<Role> {
    return this.http.put<Role>(`${this.apiUrl}/${id}`, role);
  }

  /**
   * Deletes a role by its ID.
   * @param id The ID of the role to delete.
   * @returns An Observable indicating completion.
   */
  deleteRole(id: number): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }

  /**
   * Fetches all available permissions from the API.
   * This is needed to populate the checkboxes in the role creation form.
   * Assuming permissions come from a separate endpoint or can be part of role data.
   * Adjust URL if permissions are served from a different base.
   * @returns An Observable of an array of Permission objects.
   */
  getAllPermissions(): Observable<Permission[]> {
    // Assuming permissions are at /api/permissions
    return this.http.get<Permission[]>(`${environment.URL_SERVICIOS}/permissions`);
  }
}
