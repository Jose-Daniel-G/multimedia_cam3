// index.component.ts
import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { RoleService } from '../../../core/services/role.service';
import { HttpErrorResponse } from '@angular/common/http';
// Importa Role, Permission y PaginationData. ApiResponse ya no se usará aquí directamente para la lista.
import { Role, Permission, PaginationData } from '../../../core/models/role.model';
import { AuthService } from '../../../core/services/auth.service';
import { ModalService } from '../../../shared/services/modal.service';

@Component({
  selector: 'app-roles-index',
  standalone: false,
  templateUrl: './index.component.html',
  styleUrls: ['./index.component.css'],
})
// En src/app/modules/roles/index/index.component.ts
// ... (asegúrate de que los imports y las propiedades como totalItems, itemsPerPage, etc. estén correctas)

export class IndexComponent implements OnInit {
  roles: Role[] = [];
  errorMessage: string = '';
  successMessage: string = '';

  totalItems: number = 0;
  itemsPerPage: number = 10;
  currentPage: number = 1;

  // Propiedades para el modal de confirmación (si aún las usas directamente o si ModalService las requiere)
  // Si tu ModalService maneja completamente el estado del modal (como se ve en el HTML),
  // estas propiedades podrían no ser estrictamente necesarias aquí, pero no hacen daño.
  showConfirmationModal: boolean = false; // El HTML usa modalService.confirmation$, así que esta no controla el modal directamente
  modalMessage: string = ''; // El HTML usa modalService.confirmation$, así que esta no controla el mensaje directamente
  roleToDeleteId: number | null = null; // Esta sí es necesaria para guardar el ID a eliminar

  constructor(
    private roleService: RoleService,
    private router: Router,
    private authService: AuthService,
    public modalService: ModalService // Asegúrate de que ModalService esté importado e inyectado
  ) {}

  ngOnInit(): void {
    this.loadRoles();
  }

  loadRoles(): void {
    this.errorMessage = '';
    this.roleService.getRoles(this.currentPage, this.itemsPerPage).subscribe({
      next: (response: PaginationData<Role[]>) => {
        console.log('API Response:', response);
        this.roles = response.data || [];
        this.totalItems = response.total;
        this.currentPage = response.current_page;
        this.itemsPerPage = response.per_page;
      },
      error: (err: HttpErrorResponse) => {
        console.error('Error al cargar roles:', err);
        this.errorMessage = err.error?.message || 'Error al cargar los roles.';
        this.roles = [];
      },
    });
  }

  // **** AÑADE ESTOS MÉTODOS ****

  /**
   * Abre el modal de confirmación antes de eliminar un rol.
   * Utiliza el ModalService para manejar la lógica del modal.
   * @param id El ID del rol a eliminar.
   */
  confirmDeleteRole(id: number): void {
    this.roleToDeleteId = id; // Guarda el ID del rol a eliminar
    this.modalService.openConfirmation(
      '¿Estás seguro de que deseas eliminar este rol? Esta acción no se puede deshacer.'
    ).then((confirmed) => {
      // Cuando el modalService resuelve la promesa, ejecuta la lógica
      if (confirmed && this.roleToDeleteId !== null) {
        this.deleteRole(this.roleToDeleteId);
      }
      this.roleToDeleteId = null; // Resetea el ID después de la acción
    });
  }

  /**
   * Realiza la eliminación real del rol después de la confirmación.
   * @param id El ID del rol a eliminar.
   */
  deleteRole(id: number): void {
    this.errorMessage = ''; // Limpia mensajes anteriores
    this.successMessage = '';

    this.roleService.deleteRole(id).subscribe({
      next: (response) => {
        this.successMessage = response.message || 'Rol eliminado exitosamente.';
        this.loadRoles(); // Recarga la lista de roles para reflejar el cambio
      },
      error: (err: HttpErrorResponse) => {
        console.error('Error al eliminar rol:', err);
        this.errorMessage = err.error?.message || 'Error al eliminar el rol.';
      },
    });
  }

  // **** FIN DE LOS MÉTODOS A AÑADIR ****

  getPermissionNames(permissions: Permission[] | undefined): string {
    return (permissions || []).map((p) => p.name).join(', ');
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadRoles();
  }

  editRole(id: number): void {
    this.router.navigate(['/dashboard/roles', id, 'edit']);
  }

  checkPermission(permissionName: string): boolean {
    const hasPerm = this.authService.hasPermission(permissionName);
    return hasPerm;
  }
}