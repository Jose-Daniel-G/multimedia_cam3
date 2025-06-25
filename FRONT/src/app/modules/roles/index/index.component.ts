import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { RoleService } from '../../../core/services/role.service';
import { HttpErrorResponse } from '@angular/common/http';
// Importa todos los modelos necesarios, incluyendo ApiResponse y Role
import { Role, Permission, ApiResponse } from '../../../core/models/role.model';
import { AuthService } from '../../../core/services/auth.service'; // Needed for checkPermission
import { ModalService } from '../../../shared/services/modal.service'; // Import ModalService

@Component({
  selector: 'app-roles-index',
  standalone: false,
  templateUrl: './index.component.html', // THIS IS THE CORRECT TEMPLATE FOR IndexComponent
  styleUrls: ['./index.component.css'],
})
export class IndexComponent implements OnInit {
  roles: Role[] = [];
  errorMessage: string = '';
  successMessage: string = '';

  totalItems: number = 0;
  itemsPerPage: number = 10;
  currentPage: number = 1;

  // Properties for the confirmation modal
  showConfirmationModal: boolean = false;
  modalMessage: string = '';
  roleToDeleteId: number | null = null;

  constructor(
    private roleService: RoleService,
    private router: Router,
    private authService: AuthService, // Inject AuthService
    public modalService: ModalService // Changed to public
  ) {}

  ngOnInit(): void {
    this.loadRoles();
  }

  loadRoles(): void {
    this.errorMessage = ''; // Clear previous errors
    // roleService.getRoles now correctly returns ApiResponse<Role[]> (where ApiResponse contains pagination)
    this.roleService.getRoles(this.currentPage, this.itemsPerPage).subscribe({
      next: (response: ApiResponse<Role[]>) => { // Correct type: ApiResponse<Role[]>
        this.roles = response.data.data || []; // Accede a los roles desde response.data.data y asegura que sea un array
        this.totalItems = response.data.total; // Accede a total desde response.data
        this.currentPage = response.data.current_page; // Accede a current_page desde response.data
        this.itemsPerPage = response.data.per_page; // Accede a per_page desde response.data
      },
      error: (err: HttpErrorResponse) => {
        console.error('Error al cargar roles:', err);
        this.errorMessage = err.error?.message || 'Error al cargar los roles.';
        this.roles = []; // Ensure roles is an empty array on error
      },
    });
  }

  // Method to trigger the confirmation modal
  confirmDeleteRole(id: number): void {
    this.roleToDeleteId = id;
    this.modalService.confirm('¿Estás seguro de que quieres eliminar este rol?').then((confirmed) => {
      if (confirmed && this.roleToDeleteId !== null) {
        this.deleteRole(this.roleToDeleteId);
      }
      this.roleToDeleteId = null; // Reset ID
    });
  }

  // Actual delete logic (called after confirmation)
  deleteRole(id: number): void {
    this.errorMessage = '';
    this.successMessage = '';

    this.roleService.deleteRole(id).subscribe({
      next: (response) => {
        this.successMessage = response.message || 'Rol eliminado exitosamente.';
        this.loadRoles(); // Reload roles list
      },
      error: (err: HttpErrorResponse) => {
        console.error('Error al eliminar rol:', err);
        this.errorMessage = err.error?.message || 'Error al eliminar el rol.';
      },
    });
  }

  getPermissionNames(permissions: Permission[] | undefined): string { // Allow undefined for safety
    return (permissions || []).map((p) => p.name).join(', ');
  }

  onPageChange(page: number): void {
    this.currentPage = page;
    this.loadRoles(); // Reload roles for the new page
  }

  editRole(id: number): void {
    this.router.navigate(['/dashboard/roles', id, 'edit']); // Adjusted to 'edit' route
  }

  // Método para verificar permisos (necesita AuthService)
  checkPermission(permissionName: string): boolean {
    const hasPerm = this.authService.hasPermission(permissionName);
    console.log(`[IndexComponent] checkPermission('${permissionName}'): ${hasPerm}`);
    return hasPerm;
  }
}