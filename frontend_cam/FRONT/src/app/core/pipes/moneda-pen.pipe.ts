import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'monedaPEN',
  standalone: true // Para evitar importarlo en un módulo
})
export class MonedaPenPipe implements PipeTransform {
  transform(value: number): string {
    if (value == null || isNaN(value)) return 'S/ 0.00';
    return `S/ ${value.toFixed(2)}`; // Formato con dos decimales
  }
}
