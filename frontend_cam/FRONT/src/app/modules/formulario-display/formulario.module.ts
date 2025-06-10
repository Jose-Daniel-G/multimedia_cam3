// src/app/modules/formulario-display/formulario-display.module.ts
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common'; // Necesitas CommonModule para directivas como *ngIf, *ngFor
// import { HttpClientModule } from '@angular/common/http'; // ¡Importa HttpClientModule aquí!
import { FormularioComponent } from './formulario/formulario.component';


@NgModule({
  declarations: [
    FormularioComponent // Declara el componente
  ],
  imports: [
    CommonModule, // Necesario para directivas estructurales (ngIf, ngFor)
    // HttpClientModule // Cada módulo que use HttpClient necesita importarlo
  ],
  exports: [
    // FormularioComponent // Exporta el componente para que pueda ser usado por otros módulos
  ]
})
export class FormularioDisplayModule { }