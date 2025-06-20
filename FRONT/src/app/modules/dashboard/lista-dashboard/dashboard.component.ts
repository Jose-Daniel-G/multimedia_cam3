import { AfterViewInit, Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { NgClass, NgFor } from '@angular/common';
import { MonedaPenPipe } from '../../../core/pipes/moneda-pen.pipe';


@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [NgFor, NgClass, MonedaPenPipe, ReactiveFormsModule],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css'
})
export class DashboardComponent implements OnInit{

  filtroForm: FormGroup;
  idUsuario = 1; // Se obtiene del login
  idPerfil = 1; // Se obtiene del login
  idRol = 1; // Se obtiene del login

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

  constructor(private fb: FormBuilder) {
    const fechaActual = new Date();
    this.filtroForm = this.fb.group({
      mes: [fechaActual.getMonth() + 1],
      anio: [fechaActual.getFullYear()]
    });
  }

  ngOnInit(): void {
    // this.obtenerSubvenciones();

    // Escuchar cambios en los filtros y actualizar automáticamente
    this.filtroForm.valueChanges.subscribe(() => {
      // this.obtenerSubvenciones();
    });
  }

  // obtenerSubvenciones(): void {
  //   const { mes, anio } = this.filtroForm.value;
  //   this.subvencionService.obtenerSubvenciones(mes, anio).subscribe({
  //     next: (data) => {
  //       this.subvenciones = data;
  //       console.log("listadfo", this.subvenciones);
  //     },
  //     error: (err) => {
  //       console.error('Error obteniendo subvenciones:', err);
  //     }
  //   });
  // }


  // buscar(): void {
  //   this.obtenerSubvenciones();
  // }

  // editarMonto(subvencion: Subvencion): void {
  //   if (this.idPerfil !== 1 || this.idRol !== 1) {
  //     alert('No tienes permisos para editar montos.');
  //     return;
  //   }

  //   const nuevoMonto = prompt(`Ingrese nuevo monto para ${subvencion.colegio}:`, subvencion.monto.toString());
  //   if (nuevoMonto !== null) {
  //     const monto = parseFloat(nuevoMonto);
  //     if (isNaN(monto)) {
  //       alert('Ingrese un valor numérico válido.');
  //       return;
  //     }

  //     const { mes, anio } = this.filtroForm.value;

  //     // Verificar si ya existe el monto
  //     if (subvencion.monto > 0) {
  //       this.subvencionService.actualizarMonto(subvencion.idComite, this.idUsuario, monto, mes, anio).subscribe({
  //         next: () => {
  //           alert('Monto actualizado correctamente.');
  //           this.obtenerSubvenciones();
  //         },
  //         error: (err) => console.error('Error actualizando monto:', err)
  //       });
  //     } else {
  //       this.subvencionService.registrarMonto(subvencion.idComite, this.idUsuario, monto, mes, anio).subscribe({
  //         next: () => {
  //           alert('Monto registrado correctamente.');
  //           this.obtenerSubvenciones();
  //         },
  //         error: (err) => console.error('Error registrando monto:', err)
  //       });
  //     }
  //   }
  // }

}
