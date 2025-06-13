// src/app/shared/components/shared.module.ts
import { NgModule } from '@angular/core';
import { CommonModule, NgClass, NgFor, NgIf } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MessageComponent } from './message/message.component'; // ✅ standalone
import { PaginationComponent } from './pagination/pagination.component'; // ✅ standalone

@NgModule({
  imports: [
    CommonModule,
    MessageComponent, // ✅ IMPORTAR primero
    PaginationComponent
  ],
  exports: [
    MessageComponent, // ✅ Ahora sí puedes exportarlo
    PaginationComponent,
    CommonModule,NgFor,NgIf,NgClass
  ]
})
export class SharedModule {}
