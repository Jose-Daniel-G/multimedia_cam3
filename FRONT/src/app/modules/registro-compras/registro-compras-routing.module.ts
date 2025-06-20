import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ListaRegistrosComponent } from './lista-registros/lista-registros.component';

const routes: Routes = [
  { path: '', component: ListaRegistrosComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class RegistroComprasRoutingModule { }
