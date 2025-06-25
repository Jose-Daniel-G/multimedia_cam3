import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

interface ConfirmationData {
  message: string;
  show: boolean;
  callback: (confirmed: boolean) => void;
}

@Injectable({
  providedIn: 'root'
})
export class ModalService {
  private confirmationSubject = new BehaviorSubject<ConfirmationData | null>(null);
  public confirmation$: Observable<ConfirmationData | null> = this.confirmationSubject.asObservable();

  constructor() { }

  /**
   * Muestra el modal de confirmación.
   * @param message El mensaje a mostrar en el modal.
   * @returns Una promesa que se resuelve con 'true' si el usuario confirma, o 'false' si cancela.
   */
  confirm(message: string): Promise<boolean> {
    return new Promise((resolve) => {
      this.confirmationSubject.next({
        message,
        show: true,
        callback: (confirmed: boolean) => {
          this.confirmationSubject.next(null); // Hide modal
          resolve(confirmed);
        }
      });
    });
  }

  // Método interno para que el modal llame cuando se confirme o cancele
  private handleConfirmation(confirmed: boolean): void {
    const current = this.confirmationSubject.getValue();
    if (current && current.callback) {
      current.callback(confirmed);
    }
  }

  // Métodos que el componente modal invocará
  onConfirm(): void {
    this.handleConfirmation(true);
  }

  onCancel(): void {
    this.handleConfirmation(false);
  }
}