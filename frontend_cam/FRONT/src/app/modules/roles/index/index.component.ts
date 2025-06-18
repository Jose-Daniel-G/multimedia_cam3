import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router'; // Import Router for navigation
import { RoleService } from '../../../core/services/role.service';
import { Role , Permission} from '../../../core/models/role.model'; // Assuming Role is defined here
import { AuthService } from '../../../core/services/auth.service'; // Needed for checkPermission
@Component({
  selector: 'app-roles-index',
  standalone: false, // Confirmado: NO ES STANDALONE
  templateUrl: './index.component.html',
  styleUrls: ['./index.component.css'],
})
export class IndexComponent implements OnInit {
  roles: Role[] = [];
  errorMessage: string = '';
  successMessage: string = '';

  // Propiedades para paginación (si es client-side o si la API proporciona estos datos)
  totalItems: number = 0;
  itemsPerPage: number = 10;
  currentPage: number = 1;

  constructor(
    private roleService: RoleService,
    private router: Router, // Inject Router
    private authService: AuthService // Inject AuthService
  ) {}

  ngOnInit(): void {
    this.loadRoles();
  }

  loadRoles(): void { 
    this.roleService.getRoles().subscribe({
      next: (response: { message: string; data: Role[] }) => { 
        this.roles = response.data; // Accede a 'data'
        this.totalItems = response.data.length; 
      },
      error: (err: any) => {
        console.error('Error al cargar roles:', err);
        this.errorMessage = err.error?.message || 'Error al cargar los roles.';
      },
    });
  }

  deleteRole(id: number): void {
    if (confirm('¿Estás seguro de que quieres eliminar este rol?')) {
      this.roleService.deleteRole(id).subscribe({
        next: (response: { message: string }) => {
          this.successMessage =
            response.message || 'Rol eliminado exitosamente.';
          this.loadRoles(); // Recargar la lista de roles
        },
        error: (err: any) => {
          console.error('Error al eliminar rol:', err);
          this.errorMessage = err.error?.message || 'Error al eliminar el rol.';
        },
      });
    }
  }

  // Método para verificar permisos (necesita AuthService)
  checkPermission(permissionName: string): boolean {
    return this.authService.hasPermission(permissionName);
  } 
  
  getPermissionNames(permissions: Permission[]): string {
    return permissions.map((p) => p.name).join(', ');
  }

  onPageChange(page: number): void {
    this.currentPage = page; 
    this.updatePaginatedRoles(); // Esto es para paginación del lado del cliente
  }
 
  updatePaginatedRoles(): void { 
    const start = (this.currentPage - 1) * this.itemsPerPage;
    const end = start + this.itemsPerPage;
  }
  hasPermission(permissionName: string): boolean {
    return this.authService.hasPermission(permissionName);
  }
  editRole(id: number): void {
    this.router.navigate(['/admin/roles/edit', id]); // Ajusta la ruta según tus rutas reales
  }
  
}
