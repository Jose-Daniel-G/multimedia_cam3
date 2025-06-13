import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-message',
  standalone: true,
  templateUrl: './message.component.html'
})
export class MessageComponent {
  @Input() successMessage: string | null = null;
  @Input() errorMessage: string | null = null;
}
