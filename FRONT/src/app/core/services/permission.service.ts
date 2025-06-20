import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Permission } from '../models/role.model';

@Injectable({
  providedIn: 'root',
})
export class PermissionService {
  private apiUrl = `${environment.URL_SERVICIOS}/permissions`;

  constructor(private http: HttpClient) {}

  getAll(): Observable<Permission[]> {
    return this.http.get<Permission[]>(this.apiUrl);
  }

  getById(id: number): Observable<Permission> {
    return this.http.get<Permission>(`${this.apiUrl}/${id}`);
  }

  create(data: { name: string }): Observable<Permission> {
    return this.http.post<Permission>(this.apiUrl, data);
  }

  update(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/${id}`, data);
  }

  delete(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
  getPermissions(): Observable<{ message: string; data: Permission[] }> {
    return this.http.get<{ message: string; data: Permission[] }>(this.apiUrl);
  }
}
