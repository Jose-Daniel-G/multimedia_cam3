// src/app/modules/permissions/services/permission.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface Permission {
  id: number;
  name: string;
  created_at: string;
}

@Injectable({
  providedIn: 'root'
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

  update(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/${id}`, data);
  }

  delete(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}
