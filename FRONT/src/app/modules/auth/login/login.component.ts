import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service'; // Asegúrate de que la ruta sea correcta
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';

// Importa las interfaces de tus modelos para tipado seguro
// Asegúrate de que la ruta sea correcta y que AuthUser esté importado
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
  errorMessage: string = ''; // Variable para mostrar mensajes de error en el HTML

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', Validators.required]
    });
  }

  /**
   * Maneja el envío del formulario de login.
   * Realiza la llamada al servicio de autenticación y gestiona la respuesta.
   */
  login(): void {
    this.errorMessage = ''; // Resetea cualquier mensaje de error anterior

    if (this.loginForm.invalid) {
      this.errorMessage = 'Por favor, ingresa un email y contraseña válidos.';
      this.loginForm.markAllAsTouched();
      console.warn('LoginComponent: Formulario inválido. Detalles:', this.loginForm.errors);
      return;
    }

    const credentials: LoginRequest = this.loginForm.value;

    console.log('LoginComponent: Enviando credenciales al AuthService para login.');

    // Se suscribe al Observable que devuelve el AuthService.login()
    // El AuthService ya maneja el flujo completo (CSRF, POST login, guardar token, GET /api/user).
    // La respuesta 'userResponse' de este subscribe será el AuthUser devuelto por /api/user.
    this.authService.login(credentials).subscribe({
      // ¡CORRECCIÓN AQUÍ! Cambiado el tipo de 'userResponse' de 'UsuarioLoginResponse' a 'AuthUser'.
      next: (userResponse: AuthUser) => { 
        // Si llegamos aquí, el login fue exitoso, el token se guardó en localStorage (en AuthService),
        // y los datos del usuario se obtuvieron de /api/user.
        console.log('LoginComponent: Login exitoso. Usuario autenticado:', userResponse);
        console.log('LoginComponent: Redireccionando a /dashboard.');
        this.router.navigate(['/dashboard']); // Redirigir al dashboard
      },
      error: (err) => {
        console.error('LoginComponent: Error en login:', err);

        // Intenta obtener un mensaje de error más específico del backend si lo proporciona
        if (err.error && err.error.message) {
          this.errorMessage = err.error.message; // Mensaje del servidor (ej. 'Credenciales incorrectas')
        } else if (err.status === 401) {
          this.errorMessage = 'Credenciales incorrectas.';
        } else if (err.status === 419) {
          this.errorMessage = 'La sesión ha expirado o un problema de seguridad. Por favor, recarga la página e intenta de nuevo.';
        } else if (err.status === 500) {
          this.errorMessage = 'Error interno del servidor. Por favor, intenta de nuevo más tarde.';
        } else {
          this.errorMessage = 'Ocurrió un error inesperado al iniciar sesión.';
        }
        // Usar alert() para depuración, pero considera mostrar el mensaje directamente en el HTML
        alert(this.errorMessage);
      }
    });
  }
}
