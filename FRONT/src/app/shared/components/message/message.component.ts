import { CommonModule } from '@angular/common';
import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-message',
  standalone: true, // Este componente es standalone
  imports: [CommonModule], // No necesita módulos adicionales
  templateUrl: './message.component.html'
})
export class MessageComponent {
  @Input() successMessage: string | null = null;
  @Input() errorMessage: string | null = null;
}
