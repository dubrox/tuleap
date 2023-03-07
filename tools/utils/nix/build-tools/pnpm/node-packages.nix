# This file has been generated by node2nix 1.11.1. Do not edit!

{nodeEnv, fetchurl, fetchgit, nix-gitignore, stdenv, lib, globalBuildInputs ? []}:

let
  sources = {};
in
{
  "pnpm-^7" = nodeEnv.buildNodePackage {
    name = "pnpm";
    packageName = "pnpm";
    version = "7.29.0";
    src = fetchurl {
      url = "https://registry.npmjs.org/pnpm/-/pnpm-7.29.0.tgz";
      sha512 = "SyJGcUK7gmLSJYjspL6iVOR9CS7ARYA7K0JmJkjn+MMxEJBUwzc0XdPBNwh5q8dxs+iR2ZQBalmI1lI24GRVOA==";
    };
    buildInputs = globalBuildInputs;
    meta = {
      description = "Fast, disk space efficient package manager";
      homepage = "https://pnpm.io";
      license = "MIT";
    };
    production = true;
    bypassCache = true;
    reconstructLock = true;
  };
}
