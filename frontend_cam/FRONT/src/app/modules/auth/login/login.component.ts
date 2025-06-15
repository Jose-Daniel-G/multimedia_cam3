import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';

// Importa las interfaces de tus modelos para tipado seguro
import { LoginRequest, AuthUser } from '../../../core/models/login.model';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  loginForm: FormGroup;
  errorMessage: string = ''; // Añadir una variable para mostrar mensajes de error en el HTML

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]], // Agregado Validators.email para mejor validación
      password: ['', Validators.required]                   // Cambiado de 'passwordUsuario' a 'password'
    });
  }

  login() {
    // Resetea cualquier mensaje de error anterior
    this.errorMessage = '';

    if (this.loginForm.invalid) {
      this.errorMessage = 'Por favor, ingresa un email y contraseña válidos.';
      // Opcional: marca los campos como tocados para que los mensajes de validación HTML aparezcan
      this.loginForm.markAllAsTouched();
      return;
    }

    // Castea a LoginRequest para asegurar el tipado
    const credentials: LoginRequest = this.loginForm.value;

    this.authService.login(credentials).subscribe({
      next: (response: AuthUser) => { // Tipado de la respuesta
        this.authService.setCurrentUser(response); // ya no da error TS2345
        console.log('Login exitoso. Usuario:', this.authService.getCurrentUser());
        this.router.navigate(['/dashboard']); // Redirige al dashboard o a la ruta que desees 
      },
      error: (err) => {
        console.error('Error en login:', err);

        // Intenta obtener un mensaje de error más específico del backend si lo proporciona
        if (err.error && err.error.error) {
          this.errorMessage = err.error.error; // Como el backend devuelve {error: 'No autorizado'}
        } else if (err.status === 401) {
          this.errorMessage = 'Credenciales incorrectas.'; // Mensaje genérico para 401
        } else if (err.status === 500) {
          this.errorMessage = 'Error interno del servidor. Intenta de nuevo más tarde.';
        } else {
          this.errorMessage = 'Ocurrió un error inesperado al iniciar sesión.';
        }
        alert(this.errorMessage); // Mantener la alerta por ahora, puedes quitarla si usas solo errorMessage en HTML
      }
    });
  }
}