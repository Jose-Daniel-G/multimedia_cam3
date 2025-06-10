import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Racion } from '../models/racion.model';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class RacionService {

  private apiUrl = 'http://localhost:5202/api/Racion';

  constructor(private http: HttpClient) {}

  listarRaciones(): Observable<Racion[]> {
    return this.http.get<Racion[]>(`${this.apiUrl}/listar`);
  }
}
