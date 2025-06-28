// src/app/shared/services/modal.service.ts

import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, Subject } from 'rxjs';

// Interfaz para los datos del modal de confirmación
export interface ConfirmationData {
  show: boolean;
  message: string;
  // Puedes añadir más propiedades si tu modal de confirmación las necesita,
  // como un título, texto de botones, etc.
}

@Injectable({
  providedIn: 'root'
})
export class ModalService {
  // BehaviorSubject para mantener y emitir el estado actual del modal de confirmación
  private confirmationSubject = new BehaviorSubject<ConfirmationData | null>(null);
  // Observable público para que los componentes se suscriban al estado del modal
  public confirmation$: Observable<ConfirmationData | null> = this.confirmationSubject.asObservable();

  // Subject para emitir el resultado de la acción del usuario (confirmar o cancelar)
  private confirmActionSubject = new Subject<boolean>();

  constructor() {}

  /**
   * Abre el modal de confirmación con un mensaje dado y devuelve una Promesa que resuelve a un booleano.
   * El booleano será 'true' si el usuario confirma, 'false' si cancela.
   * @param message El mensaje a mostrar en el modal de confirmación.
   * @returns Promise<boolean> Una promesa que resuelve con el resultado de la acción del usuario.
   */
  openConfirmation(message: string): Promise<boolean> {
    // Establece el estado del modal a visible con el mensaje
    this.confirmationSubject.next({ show: true, message });

    // Crea y devuelve una nueva promesa
    return new Promise((resolve) => {
      // Suscríbete al Subject que emitirá el resultado de la acción del usuario
      const subscription = this.confirmActionSubject.subscribe(result => {
        resolve(result); // Resuelve la promesa con el resultado
        subscription.unsubscribe(); // Desuscríbete para evitar fugas de memoria
        this.closeConfirmation(); // Cierra el modal después de la acción
      });
    });
  }

  /**
   * Método llamado por el componente del modal de confirmación cuando el usuario confirma.
   * Emite 'true' al confirmActionSubject.
   */
  onConfirm(): void {
    this.confirmActionSubject.next(true);
  }

  /**
   * Método llamado por el componente del modal de confirmación cuando el usuario cancela.
   * Emite 'false' al confirmActionSubject.
   */
  onCancel(): void {
    this.confirmActionSubject.next(false);
  }

  /**
   * Cierra explícitamente el modal de confirmación.
   */
  closeConfirmation(): void {
    this.confirmationSubject.next(null); // O { show: false, message: '' }
  }
}