import { Component, Input, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-confirmation-modal',
  standalone: false, // Will be imported into SharedModule
  templateUrl: './confirmation-modal.component.html',
  styleUrls: ['./confirmation-modal.component.css']
})
export class ConfirmationModalComponent {
  @Input() message: string = '¿Estás seguro?';
  @Input() show: boolean = false; // Controls visibility of the modal

  @Output() confirm = new EventEmitter<void>();
  @Output() cancel = new EventEmitter<void>();

  constructor() { }

  onConfirm(): void {
    this.confirm.emit();
    this.show = false; // Hide modal after confirmation
  }

  onCancel(): void {
    this.cancel.emit();
    this.show = false; // Hide modal on cancel
  }
}