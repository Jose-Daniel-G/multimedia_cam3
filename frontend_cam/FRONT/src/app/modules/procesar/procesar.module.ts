// src/app/modules/formulario-display/formulario-display.module.ts
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common'; // Necesitas CommonModule para directivas como *ngIf, *ngFor
import { ProcesarRoutingModule } from './procesar-routing.module';
import { ProcesarComponent } from './list-procesar/procesar.component';
import { LinebreaksPipe } from '../../core/pipes/linebreaks.pipe';
import { EnProcesoComponent } from './en-proceso/en-proceso.component';
@NgModule({
  declarations: [ProcesarComponent, EnProcesoComponent],
  imports: [
    CommonModule, ProcesarRoutingModule, LinebreaksPipe // Necesario para directivas estructurales (ngIf, ngFor)
  ],
  exports: [
  ]
})
export class ProcesarModule { }