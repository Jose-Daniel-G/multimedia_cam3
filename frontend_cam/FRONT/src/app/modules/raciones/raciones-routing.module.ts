import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { RacionesComponent } from './lista-raciones/raciones.component';

const routes: Routes = [
  { path: '', component: RacionesComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class RacionesRoutingModule { }
