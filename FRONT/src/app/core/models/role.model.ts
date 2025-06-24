// src/app/core/models/role.model.ts

/**
 * Interface for the Permission model as received from the backend.
 */
export interface Permission {
  id: number;
  name: string;
  guard_name: string;
  created_at: string;
  updated_at?: string;
  pivot?: { // 'pivot' is optional as it only appears within role.permissions due to many-to-many relationship
    role_id: number;
    permission_id: number;
  };
}

/**
 * Interface for a Role object as received from the API (e.g., in index/show responses).
 * This includes the full Permission objects for the 'permissions' array.
 */
export interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at?: string;
  updated_at?: string;
  permissions?: Permission[]; // When receiving, 'permissions' is an array of Permission objects
}

/**
 * Interface for the payload when CREATING a new role via API.
 * The 'permission' property here is an array of numbers (permission IDs)
 * because that's what your Laravel backend expects for syncing.
 */
export interface CreateRolePayload {
  name: string;
  guard_name: string; // Made required as your form defaults it and backend likely expects it
  permission?: number[]; // When sending to backend, 'permission' is an array of IDs
}

/**
 * Interface for the payload when UPDATING an existing role via API.
 * Similar to CreateRolePayload, but properties might be optional for partial updates,
 * and 'permission' is also an array of numbers (permission IDs).
 */
export interface UpdateRolePayload {
  name?: string;
  guard_name?: string;
  permission?: number[]; // When sending to backend, 'permission' is an array of IDs
}

/**
 * Interface for Laravel's pagination response structure.
 * @template T The type of data contained within the 'data' array (e.g., Role[]).
 */
export interface ApiResponse<T> { // Renamed from RoleApiResponse for generic use
  current_page: number;
  data: T; // This will be T[] (e.g., Role[]) if paginated, or T (e.g., Role) for single item
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

// Added for general API success messages
export interface SuccessMessageResponse {
  message: string;
  data?: any; // Optional data if API returns it
}
