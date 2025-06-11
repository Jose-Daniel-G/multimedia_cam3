import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ProcesarComponent } from './list-procesar/procesar.component';
import { EnProcesoComponent } from './en-proceso/en-proceso.component';

const routes: Routes = [
  { path: '', component: ProcesarComponent },
  { path: 'en-proceso', component: EnProcesoComponent,  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ProcesarRoutingModule { }
