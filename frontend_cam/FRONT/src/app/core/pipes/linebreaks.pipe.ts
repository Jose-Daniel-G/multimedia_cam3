import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'linebreaks',
  standalone: true
})
export class LinebreaksPipe implements PipeTransform {
  transform(value: string): string {
    return value?.replace(/\n/g, '<br>');
  }
}
