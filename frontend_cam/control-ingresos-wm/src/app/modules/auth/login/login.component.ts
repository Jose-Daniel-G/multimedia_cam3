import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';

// Importa las interfaces de tus modelos para tipado seguro
import { LoginRequest, UsuarioLoginResponse } from '../../../core/models/login.model'; // Asegúrate de que la ruta sea correcta

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
      // ¡CORRECCIÓN CLAVE AQUÍ!
      // Asegúrate de que los nombres de los controles coincidan con LoginRequest
      // y lo que tu backend espera para el campo de la contraseña.
      email: ['', [Validators.required, Validators.email]], // Agregado Validators.email para mejor validación
      password: ['', Validators.required] // Cambiado de 'passwordUsuario' a 'password'
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
      next: (response: UsuarioLoginResponse) => { // Tipado de la respuesta
        // ¡Manejo del token y datos del usuario!
        // Asegúrate de que la respuesta del backend contenga el token
        if (response && response.access_token) {
          this.authService.setCurrentUser(response); // Guarda toda la respuesta (incluyendo token, roles, etc.)
          console.log('Login exitoso. Usuario:', this.authService.getCurrentUser());
          this.router.navigate(['/dashboard']);
        } else {
          // Esto debería ser un error del backend si el login fue exitoso pero no hay token
          this.errorMessage = 'Login exitoso pero no se recibió token de sesión.';
          console.error('Login exitoso pero no se recibió token en la respuesta:', response);
        }
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