import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Subvencion } from '../models/subvencion.model';

@Injectable({
  providedIn: 'root'
})
export class SubvencionService {
  private apiUrl = 'http://localhost:5202/api/Subvencion';

  constructor(private http: HttpClient) {}

  obtenerSubvenciones(mes: number, anio: number): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/listar?mes=${mes}&anio=${anio}`);
  }

  // obtenerSubvenciones(mes: number, anio: number): Observable<Subvencion[]> {
  //   return this.http.get<Subvencion[]>(`${this.apiUrl}/listar?mes=${mes}&anio=${anio}`);
  // }

  registrarMonto(idComite: number, idUsuario: number, monto: number, mes: number, anio: number): Observable<any> {
    const request = { idComite, idUsuario, monto };
    return this.http.post(`${this.apiUrl}/registrar?mes=${mes}&anio=${anio}`, request);
  }

  actualizarMonto(idComite: number, idUsuario: number, monto: number, mes: number, anio: number): Observable<any> {
    const request = { idComite, idUsuario, monto, mes, anio };
    return this.http.put(`${this.apiUrl}/actualizar`, request);
  }
}
