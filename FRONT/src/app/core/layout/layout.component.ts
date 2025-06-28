import { Component, AfterViewInit } from '@angular/core';
import { RouterLink, RouterOutlet } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { AdminLteService } from '../services/admin-lte.service';

import { NgIf } from '@angular/common';
import { AuthUser } from '../models/login.model';

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [RouterLink, RouterOutlet, NgIf],
  templateUrl: './layout.component.html',
  styleUrl: './layout.component.css',
})
export class LayoutComponent {
 user: AuthUser | null = null; // Variable para almacenar el usuario

  constructor(
    private authService: AuthService,
    private adminLte: AdminLteService
  ) {
    this.user = this.authService.getCurrentUser(); // Asigna el usuario después de que el servicio esté inicializado
  }

  ngAfterViewInit(): void {
    const toggleBtn = document.querySelector('[data-lte-toggle="sidebar"]');

    toggleBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      this.adminLte.toggleSidebar(); // mejor usar el servicio
    });

    document.querySelectorAll('.nav-item.has-treeview > a').forEach((link) => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const parent = link.closest('.nav-item.has-treeview');
        parent?.classList.toggle('menu-open');
      });
    });
  }

  logout() {
    this.authService.logout().subscribe({
      next: () => {
        // Opcional: Redirigir a la página de login después de un logout exitoso
        // this.router.navigate(['/login']);
      },
      error: (err) => {
        console.error('Error durante el logout:', err);
        // Manejar el error si es necesario, pero el AuthService ya limpia el localStorage
      }
    });
  }
}
