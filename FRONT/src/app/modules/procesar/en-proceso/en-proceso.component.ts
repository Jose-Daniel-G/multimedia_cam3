import { Component } from '@angular/core';

@Component({
  selector: 'app-en-proceso',
  templateUrl: './en-proceso.component.html',
  styleUrls: ['./en-proceso.component.css']
})
export class EnProcesoComponent {
  // Mensajes
  successMessage: string | null = null;
  errorMessage: string | null = null;
  validationErrors: string[] = [];

  // Archivos de ejemplo
  excelFiles = [
    {
      id_plantilla: 'Plantilla A',
      file: 'archivo1.xlsx',
      n_registros: 10,
      n_pdfs: 5,
      porcentaje: '100%',
      estado: 'Completado',
      fecha: '2025-06-10'
    },
    {
      id_plantilla: 'Plantilla B',
      file: 'archivo2.xlsx',
      n_registros: 20,
      n_pdfs: 15,
      porcentaje: '80%',
      estado: 'Procesando',
      fecha: '2025-06-09'
    }
  ];
  organismo = {
    depe_nomb: 'Nombre del Organismo' // Cambia esto según corresponda
  };
}
