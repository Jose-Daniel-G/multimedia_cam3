import { Component, OnInit } from '@angular/core';
import { FormularioService } from '../../../core/services/formulario.service';
import { CommonModule } from '@angular/common';

// Define the interface for a question (keep this as defined previously)
interface Pregunta {
  idPregunta: number;
  nombre: string;
  etiqueta: string;
  descripcion?: string; // Made optional as it might be missing
  tipo: string;
  requerido: 'SI' | 'NO';
  sololectura: 'SI' | 'NO';
  limiteCaracteres?: number;
  funcion?: string;
  // Add other properties as needed
}

// Define the interface for the API response data (keep this)
interface FormularioData {
  idFormulario: number;
  nombre: string;
  descripcion: string;
  preguntas: Pregunta[];
}

// Define the interface for the full API response (keep this)
interface ApiResponse {
  result: string;
  data: FormularioData;
  message?: string;
}

@Component({
  selector: 'app-formulario',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './formulario.component.html',
  styleUrls: ['./formulario.component.css']
})
export class FormularioComponent implements OnInit {
  formularioData: FormularioData | null = null;
  mesesChequeo: Pregunta[] = [];
  loading: boolean = true;
  error: string | null = null;

  // Define the set of month names for quick lookup
  private nombresMesesSet = new Set([
    'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
    'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'
  ]);

  constructor(private formularioService: FormularioService) { }

  ngOnInit(): void {
    this.getFormulario();
  }

  getFormulario(): void {
    this.loading = true;
    this.error = null;
    const idFormulario = 103;

    this.formularioService.getFormularioComplete(idFormulario).subscribe({
      next: (response: ApiResponse) => {
        if (response.result === 'OK' && response.data) {
          this.formularioData = response.data;
          const preguntas: Pregunta[] = response.data.preguntas || [];

          // Filter for month checkboxes directly in TS
          this.mesesChequeo = preguntas.filter((p: Pregunta) =>
            p.tipo === 'CHEQUEO' &&
            p.descripcion && // Ensure descripcion exists before calling toUpperCase
            this.nombresMesesSet.has(p.descripcion.toUpperCase()) // Use .has() for Set lookup
          );

        } else {
          this.error = 'La API no devolvió un resultado OK o no tiene datos.';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Error al obtener los datos del formulario: ' + err.message;
        this.loading = false;
        console.error(err);
      }
    });
  }

  /**
   * Helper method to determine if a question is a "special" checkbox (month or year).
   * This logic is too complex for direct template expression.
   */
  isSpecialCheckbox(pregunta: Pregunta): boolean {
    if (pregunta.tipo !== 'CHEQUEO') {
      return false; // Only checkboxes can be special checkboxes
    }

    // Check if it's a month checkbox based on its description
    if (pregunta.descripcion && this.nombresMesesSet.has(pregunta.descripcion.toUpperCase())) {
      return true;
    }

    // Check if it's an 'AÑOYYYY' checkbox based on its name
    // Assuming "AÑO" followed by digits (e.g., AÑO2023)
    if (pregunta.nombre?.toUpperCase().startsWith('AÑO')) {
      // You might want a more robust regex here if "AÑO" can appear elsewhere in the name
      // E.g., /^(AÑO)\d{4}$/.test(pregunta.nombre.toUpperCase())
      return true;
    }

    return false; // Not a special checkbox
  }
}