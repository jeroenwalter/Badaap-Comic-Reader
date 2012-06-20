@echo off

pushd .

call sencha app build testing

popd

del build.sqlite

pause
