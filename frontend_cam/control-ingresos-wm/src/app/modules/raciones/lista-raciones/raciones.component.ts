import { NgFor, NgIf } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { Racion } from '../../../core/models/racion.model';
import { RacionService } from '../../../core/services/racion.service';
import { FormBuilder, FormGroup, FormsModule } from '@angular/forms';

@Component({
  selector: 'app-raciones',
  standalone: false,
  //imports: [FormsModule, NgFor, NgIf],
  templateUrl: './raciones.component.html',
  styleUrl: './raciones.component.css'
})
export class RacionesComponent implements OnInit {
  raciones: Racion[] = [];
  fechasDisponibles: string[] = [];
  racionesSeleccionadas: any[] = []; // Lista de raciones seleccionadas
  nuevaRacion = {
    fecha: '',
    nombreRacion: '',
    observaciones: ''
  };

  filtroForm: FormGroup;
  meses = [
    { id: 1, nombre: 'Enero' },
    { id: 2, nombre: 'Febrero' },
    { id: 3, nombre: 'Marzo' },
    { id: 4, nombre: 'Abril' },
    { id: 5, nombre: 'Mayo' },
    { id: 6, nombre: 'Junio' },
    { id: 7, nombre: 'Julio' },
    { id: 8, nombre: 'Agosto' },
    { id: 9, nombre: 'Septiembre' },
    { id: 10, nombre: 'Octubre' },
    { id: 11, nombre: 'Noviembre' },
    { id: 12, nombre: 'Diciembre' }
  ];

  constructor(private racionService: RacionService, private fb: FormBuilder) {
    const fechaActual = new Date();
    this.filtroForm = this.fb.group({
      mes: [fechaActual.getMonth() + 1],
      anio: [fechaActual.getFullYear()],
      beneficiarios: [0],
    });
  }

  ngOnInit() {
    this.generarFechasDelMes();
    this.obtenerRaciones();
  }

  obtenerRaciones(): void {
    this.racionService.listarRaciones().subscribe({
      next: (data) => {
        this.raciones = data;
        console.log('✅ Raciones obtenidas:', this.raciones);
      },
      error: (err) => {
        console.error('❌ Error al obtener raciones:', err);
      },
    });
  }

  generarFechasDelMes(): void {
    const mesActual = new Date().getMonth();
    const anioActual = new Date().getFullYear();
    const diasEnMes = new Date(anioActual, mesActual + 1, 0).getDate();

    this.fechasDisponibles = Array.from({ length: diasEnMes }, (_, i) => {
      return new Date(anioActual, mesActual, i + 1).toISOString().split('T')[0];
    });

    console.log('📅 Fechas disponibles:', this.fechasDisponibles);
  }

  agregarRacion(): void {
    if (!this.nuevaRacion.fecha || !this.nuevaRacion.nombreRacion) {
      console.warn('⚠️ Seleccione una fecha y una ración antes de agregar.');
      return;
    }

    const nuevaFila = {
      fecha: this.nuevaRacion.fecha,
      nombreRacion: this.nuevaRacion.nombreRacion,
      observaciones: this.nuevaRacion.observaciones,
    };

    this.racionesSeleccionadas.push(nuevaFila);
    console.log('➕ Nueva ración agregada:', nuevaFila);

    // Limpiar los selects
    this.nuevaRacion = { fecha: '', nombreRacion: '', observaciones: '' };
  }


  obtenerDiaYNombre(fecha: string): string {
    const opciones: Intl.DateTimeFormatOptions = { weekday: 'long', day: 'numeric', month: 'long' };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
  }

}
