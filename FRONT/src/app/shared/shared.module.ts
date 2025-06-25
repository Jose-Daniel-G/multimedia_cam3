import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common'; // Necesario para directivas comunes como *ngIf, *ngFor, ngClass, ngStyle
import { FormsModule, ReactiveFormsModule } from '@angular/forms'; // Si tus componentes compartidos usan formularios de plantilla o reactivos
import { RouterModule } from '@angular/router'; // Si tus componentes compartidos usan routerLink

// Importa y declara tus componentes, directivas y pipes compartidos aquí.
// Si tus componentes compartidos son STANDALONE, se importarían directamente en los "imports"
// de este módulo si este módulo los usa, O directamente en los "imports" de los componentes/módulos
// que los utilicen.
// Basado en tu error anterior, PaginationComponent podría ser standalone.
import { PaginationComponent } from './components/pagination/pagination.component'; // Asegúrate de la ruta correcta
import { MessageComponent } from './components/message/message.component';
import { ConfirmationModalComponent } from './components/confirmation-modal/confirmation-modal.component';

@NgModule({
  // Si los componentes son STANDALONE, NO los declares aquí.
  // Déjalo vacío si todos los elementos que quieres compartir son Standalone.
  declarations: [ConfirmationModalComponent],
  imports: [
    CommonModule, // Siempre necesario para los módulos
    FormsModule,
    ReactiveFormsModule,
    RouterModule, // Importa si tus componentes compartidos usan routerLink
    PaginationComponent, // Importa el componente standalone si quieres que SharedModule lo reexporte.
    MessageComponent,
  ],
  exports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,
    PaginationComponent, // Exporta el componente para que otros módulos puedan usar <app-pagination>
    MessageComponent,
    ConfirmationModalComponent
  ],
})
export class SharedModule {}
