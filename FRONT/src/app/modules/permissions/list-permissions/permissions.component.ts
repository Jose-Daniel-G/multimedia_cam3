import { Component, OnInit } from '@angular/core';
import { Permission } from '../../../core/models/role.model';

@Component({
  selector: 'app-permissions',
  // Es un componente NO standalone, por lo tanto, no se incluye 'standalone: true'.
  // Si tu CLI lo puso como 'standalone: false', asegúrate de que esté así.
  standalone: false, // <-- ¡Importante!
  // No hay array 'imports' si 'standalone' es false.
  templateUrl: './permissions.component.html',
  styleUrls: ['./permissions.component.css']
})
export class PermissionsComponent implements OnInit {
  // Tipado de permissions a la interfaz Permission[]
  permissions: Permission[] = [];
  paginatedPermissions: Permission[] = []; // Tipado también a Permission[]
  
  errorMessage: string = '';
  successMessage: string = '';

  // Propiedades para paginación (coinciden con los @Input de PaginationComponent)
  currentPage: number = 1;
  itemsPerPage: number = 5; // Valor inicial
  totalItems: number = 0; // Inicializado a 0 aquí, se actualizará en ngOnInit

  constructor() {
    // El constructor se deja vacío o para inyecciones de dependencia solamente.
    // La lógica de inicialización de datos se mueve a ngOnInit.
  }

  ngOnInit(): void {
    // Simulamos permisos aquí, con los campos 'guard_name' y 'updated_at'
    this.permissions = Array.from({ length: 23 }, (_, i) => ({
      id: i + 1,
      name: `Permiso ${i + 1}`,
      guard_name: 'web', // Añadido para coincidir con tu esquema
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString() // Añadido para coincidir con tu esquema
    }));
    this.totalItems = this.permissions.length; // Actualiza totalItems después de cargar los permisos
    this.updatePaginatedPermissions();
  }

  updatePaginatedPermissions(): void {
    const start = (this.currentPage - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
    // Esto es solo para simular paginación en el frontend con datos locales
    this.paginatedPermissions = this.permissions.slice(start, end);
  }

  // Manejador del evento pageChange, el $event ya es un número gracias al EventEmitter<number>()
  onPageChange(pageNumber: number): void {
    this.currentPage = pageNumber;
    this.updatePaginatedPermissions(); // Actualiza los permisos paginados
  }

  // Ejemplo de método para eliminar
  deletePermission(id: number): void {
    if (confirm('¿Estás seguro de que quieres eliminar este permiso?')) {
      console.log(`Eliminando permiso con ID: ${id}`);
      // Lógica de eliminación con tu servicio
    }
  }
}
