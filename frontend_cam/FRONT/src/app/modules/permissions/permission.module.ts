// src/app/modules/formulario-display/formulario-display.module.ts
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common'; // Necesitas CommonModule para directivas como *ngIf, *ngFor
import { PermissionRoutingModule } from './permission-routing.module';
import { PermissionsComponent } from './list-permissions/permissions.component';
import { PaginationComponent } from '../../shared1/components/pagination/pagination.component';
import { MessageComponent } from '../../shared1/components/message/message.component';
import { ReactiveFormsModule } from '@angular/forms';
import { EditComponent } from './edit/edit.component';
import { HttpClientModule } from '@angular/common/http';



@NgModule({
  declarations: [PermissionsComponent, EditComponent, PaginationComponent, MessageComponent// EnProcesoComponent
  ],
  imports: [
    CommonModule, PermissionRoutingModule,  ReactiveFormsModule,  
    HttpClientModule // Cada módulo que use HttpClient necesita importarlo
  ],
  exports: [
  ]
})
export class PermissionModule { }