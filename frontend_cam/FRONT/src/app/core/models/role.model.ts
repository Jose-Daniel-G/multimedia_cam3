/**
 * Interfaz para el modelo de Rol.
 * Ajusta las propiedades según cómo tu backend Laravel define un rol.
 */
export interface Permission {
  id: number;
  name: string;
  created_at: string;
  updated_at?: string;
}

export interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at?: string;
  updated_at?: string;
}

// Si tu API usa paginación, necesitarás una interfaz para la respuesta completa
export interface RoleApiResponse {
  current_page: number;
  data: Role[];
  first_page_url: string | null;
  from: number | null;
  last_page: number;
  last_page_url: string | null;
  links: { url: string | null; label: string; active: boolean }[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
}