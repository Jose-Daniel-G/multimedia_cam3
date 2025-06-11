// src/app/modules/formulario-display/formulario-display.module.ts
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common'; // Necesitas CommonModule para directivas como *ngIf, *ngFor
import { ProcesarRoutingModule } from './procesar-routing.module';
import { ProcesarComponent } from './list-procesar/procesar.component';
import { LinebreaksPipe } from '../../core/pipes/linebreaks.pipe';
import { EnProcesoComponent } from './en-proceso/en-proceso.component';

// import { HttpClientModule } from '@angular/common/http'; // ¡Importa HttpClientModule aquí!


@NgModule({
  declarations: [ProcesarComponent, EnProcesoComponent],
  imports: [
    CommonModule, ProcesarRoutingModule, LinebreaksPipe // Necesario para directivas estructurales (ngIf, ngFor)
    // HttpClientModule // Cada módulo que use HttpClient necesita importarlo
  ],
  exports: [
  ]
})
export class ProcesarModule { }