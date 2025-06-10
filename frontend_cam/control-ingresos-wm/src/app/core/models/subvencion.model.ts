export interface Subvencion {
  idComite: number;
  idUsuario: number;
  monto: number;
  mes?: number;
  anio?: number;
  region: string;
  provincia: string;
  distrito: string;
  colegio: string;
  comite: string;
  estado: string;
}
