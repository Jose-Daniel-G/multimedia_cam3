// src/app/modules/permissions/create/create.component.ts
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router'; // Import Router for redirection
import { PermissionService } from '../../../core/services/permission.service'; // Adjust path
import { Permission } from '../../../core/models/role.model'; // Adjust path

@Component({
  selector: 'app-permission-create', // Changed from app-create to be more specific
  templateUrl: './create.component.html', // This path should be correct for your `ng generate` output
  styleUrls: ['./create.component.css'],
})
export class CreateComponent implements OnInit {
  permissionForm!: FormGroup;

  constructor(
    private fb: FormBuilder,
    private permissionService: PermissionService,
    private router: Router // Inject Router
  ) {}

  ngOnInit(): void {
    this.permissionForm = this.fb.group({
      name: ['', Validators.required],
    });
  }

  onSubmit(): void {
    if (this.permissionForm.valid) {
      this.permissionService.create(this.permissionForm.value as Permission).subscribe({
        next: (response) => {
          console.log('Permiso creado:', response);
          this.router.navigate(['/permissions']); // Redirect to the permissions list after creation
        },
        error: (err) => {
          console.error('Error al crear permiso:', err);
          // show error message to user
        },
      });
    }
  }
}