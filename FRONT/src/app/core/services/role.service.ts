import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Role } from '../models/role.model'; // Asegúrate de que la ruta sea correcta
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root' // Este servicio será un singleton y estará disponible en toda la aplicación
})
export class RoleService {
  private apiUrl = `${environment.URL_SERVICIOS}/roles`; // Ajusta esta URL a tu endpoint de roles en Laravel

  constructor(private http: HttpClient) {}

  /**
   * Obtiene todos los roles desde el backend.
   * @returns Un Observable que emite un array de Roles.
   */
  getRoles(): Observable<{ message: string; data: Role[] }> {
    // Asumo que tu API devuelve un objeto con 'message' y 'data' (array de roles)
    return this.http.get<{ message: string; data: Role[] }>(this.apiUrl);
  }

  /**
   * Obtiene un rol específico por su ID.
   * @param id El ID del rol.
   * @returns Un Observable que emite el rol.
   */
  getRole(id: number): Observable<{ message: string; data: Role }> {
    return this.http.get<{ message: string; data: Role }>(`${this.apiUrl}/${id}`);
  }

  /**
   * Crea un nuevo rol.
   * @param role Los datos del nuevo rol.
   * @returns Un Observable que emite el rol creado.
   */
  createRole(role: Omit<Role, 'id' | 'created_at' | 'updated_at'>): Observable<{ message: string; data: Role }> {
    return this.http.post<{ message: string; data: Role }>(this.apiUrl, role);
  }

  /**
   * Actualiza un rol existente.
   * @param id El ID del rol a actualizar.
   * @param role Los datos actualizados del rol.
   * @returns Un Observable que emite el rol actualizado.
   */
  updateRole(id: number, role: Partial<Role>): Observable<{ message: string; data: Role }> {
    return this.http.put<{ message: string; data: Role }>(`${this.apiUrl}/${id}`, role);
  }

  /**
   * Elimina un rol.
   * @param id El ID del rol a eliminar.
   * @returns Un Observable que emite la respuesta de la eliminación.
   */
  deleteRole(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.apiUrl}/${id}`);
  }
}
