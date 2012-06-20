@echo off

pushd .

call sencha app build production

popd

del build.sqlite

pause
