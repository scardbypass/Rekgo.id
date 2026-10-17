import test from 'node:test';
import assert from 'node:assert/strict';
import {parseCommand,menuText,helpText} from '../plugins/commands.js';

test('parse valid escrow request', () => {
 assert.deepEqual(parseCommand('/rekgo 10000'), {name:'rekgo',amount:10000});
 assert.deepEqual(parseCommand('/REKGO 1000'), {name:'rekgo',amount:1000});
});
test('reject invalid amounts and unknown commands', () => {
 for (const value of ['/rekgo','/rekgo -100','/rekgo 1.000','/rekgo 100000000000','hello']) {
  assert.equal(parseCommand(value),null);
 }
});
test('menu, help, account and done aliases', () => {
 for (const [value,name] of [['/menu','menu'],['/start','menu'],['/help','help'],['bantuan','help'],['akun','akun'],['/akun','akun'],['rekber done','done']]) {
  assert.equal(parseCommand(value)?.name,name);
 }
 assert.match(menuText(),/RekGo/);
 assert.match(helpText(),/09\.00/);
});
