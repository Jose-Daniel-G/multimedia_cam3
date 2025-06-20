import { Component } from '@angular/core';

@Component({
  selector: 'app-procesar',
  templateUrl: './procesar.component.html',
  styleUrl: './procesar.component.css'
})
export class ProcesarComponent {
  organismo = { depe_nomb: 'Nombre del Organismo' };

  successMessage: string | null = null;
  errorMessage: string | null = null;
  validationErrors: string[] = [];

  excelFiles = [
    {
      plantilla: 'Plantilla A',
      file: 'archivo1.xlsx',
      n_registros: 10,
      n_pdfs: 5
    },
    {
      plantilla: 'Plantilla B',
      file: 'archivo2.xlsx',
      n_registros: 20,
      n_pdfs: 15
    }
  ];

  procesarArchivo(file: string) {
    console.log('Procesando archivo:', file);
    // Aquí podrías hacer una llamada HTTP o mostrar un modal, etc.
  }
  getFileId(file: string): string {
    return 'fila-' + file.replace(/\.[^/.]+$/, '').replace(/\s+/g, '-');
  }
}
