import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
//import { DashboardComponent } from './dashboard.component';
import DashboardRoutingModule from './dashboard-routing.module';

@NgModule({
  declarations: [],
  imports: [CommonModule, DashboardRoutingModule] // 🔹 Asegurar que el módulo de rutas está importado
})
export class DashboardModule {}
