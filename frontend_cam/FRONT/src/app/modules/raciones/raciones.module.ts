import { NgModule } from '@angular/core';
import { CommonModule, NgClass, NgFor, NgIf } from '@angular/common';

import { RacionesRoutingModule } from './raciones-routing.module';
import { RacionesComponent } from './lista-raciones/raciones.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';


@NgModule({
  declarations: [RacionesComponent],
  imports: [
    CommonModule,
    RacionesRoutingModule,
    ReactiveFormsModule,
    FormsModule,
    NgFor,
    NgIf,
    NgClass
  ]
})
export class RacionesModule { }
